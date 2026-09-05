<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\Product;
use App\Models\StockMovement;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class InvoiceController extends Controller {

    /**
     * Sales tax is a rate the business sets, not a number scattered through the code.
     * It lives here rather than in the Blade template because the browser is no
     * longer the thing that calculates it.
     */
    private const TAX_RATE = 0.05;

    function InvoicePage(): View {
        return view('pages.dashboard.invoice-page');
    }

    function SalePage(): View {
        return view('pages.dashboard.sale-page');
    }

    /**
     * Record a sale.
     *
     * Two things changed here from the original and both are the same mistake:
     * the server used to believe whatever the browser told it. It took the
     * total, the tax and the payable amount straight from the request body, so
     * a hand-written POST could buy a desk lamp for a dollar; and it never
     * looked at stock, so it would cheerfully sell the twentieth of three.
     *
     * Now the request carries only what the browser is entitled to decide --
     * which products, how many, which customer, what discount was agreed -- and
     * every figure on the invoice is computed here from the prices in the
     * database.
     */
    function invoiceCreate(Request $request): JsonResponse {

        $user_id = $request->header('userID');

        $validator = Validator::make($request->all(), [
            'customer_id'           => 'required|integer',
            'discount_percent'      => 'nullable|numeric|min:0|max:100',
            'products'              => 'required|array|min:1',
            'products.*.product_id' => 'required|integer',
            'products.*.qty'        => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'failed',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // The customer must be one of this user's own, or an invoice could be
        // addressed to a stranger's customer record.
        $customer = Customer::where('user_id', $user_id)
            ->where('id', $request->input('customer_id'))
            ->first();

        if (!$customer) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Select a customer from your own list.',
            ], 422);
        }

        // Two lines for the same product are one order for the sum of them.
        // Merging first means the stock check sees the real demand rather than
        // approving each half separately.
        $requested = [];
        foreach ($request->input('products') as $line) {
            $productId = (int) $line['product_id'];
            $requested[$productId] = ($requested[$productId] ?? 0) + (int) $line['qty'];
        }

        DB::beginTransaction();

        try {
            // lockForUpdate holds these rows until the transaction ends, so two
            // tills selling the last unit at the same moment cannot both read
            // "1 left" and both succeed.
            $products = Product::where('user_id', $user_id)
                ->whereIn('id', array_keys($requested))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $lines    = [];

            foreach ($requested as $productId => $qty) {
                $product = $products->get($productId);

                if (!$product) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => 'failed',
                        'message' => 'One of those products is no longer in your catalogue.',
                    ], 422);
                }

                if ($product->stock < $qty) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => 'failed',
                        // Naming the product and the number left is the whole
                        // point -- "something went wrong" tells a cashier with a
                        // queue in front of them nothing they can act on.
                        'message' => "Not enough stock for {$product->name}: {$product->stock} left, {$qty} requested.",
                    ], 422);
                }

                $linePrice = round((float) $product->price * $qty, 2);
                $subtotal += $linePrice;

                $lines[] = [
                    'product'    => $product,
                    'qty'        => $qty,
                    'sale_price' => $linePrice,
                ];
            }

            $discountPercent = (float) $request->input('discount_percent', 0);
            $discount = round($subtotal * $discountPercent / 100, 2);
            $total    = round($subtotal - $discount, 2);
            $tax      = round($total * self::TAX_RATE, 2);
            $payable  = round($total + $tax, 2);

            $invoice = Invoice::create([
                'total'       => (string) $total,
                'discount'    => (string) $discount,
                // The column is still called vat; only the label changed.
                'vat'         => (string) $tax,
                'payable'     => (string) $payable,
                'user_id'     => $user_id,
                'customer_id' => $customer->id,
            ]);

            foreach ($lines as $line) {
                $product = $line['product'];

                InvoiceProduct::create([
                    'invoice_id' => $invoice->id,
                    'user_id'    => $user_id,
                    'product_id' => $product->id,
                    'qty'        => (string) $line['qty'],
                    'sale_price' => (string) $line['sale_price'],
                ]);

                $balanceAfter = $product->stock - $line['qty'];
                $product->decrement('stock', $line['qty']);

                StockMovement::create([
                    'user_id'       => $user_id,
                    'product_id'    => $product->id,
                    'invoice_id'    => $invoice->id,
                    'reason'        => 'sale',
                    'quantity'      => -$line['qty'],
                    'balance_after' => $balanceAfter,
                    'note'          => "Invoice #{$invoice->id}",
                ]);
            }

            DB::commit();

            return response()->json([
                'status'     => 'success',
                'message'    => 'Invoice created.',
                'invoice_id' => $invoice->id,
                'payable'    => $payable,
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'failed',
                'message' => 'Could not record the sale.',
            ], 500);
        }
    }

    function invoiceSelect(Request $request) {
        $user_id = $request->header('userID');
        return Invoice::where('user_id', $user_id)
            ->with('customer')
            ->orderByDesc('id')
            ->get();
    }

    function InvoiceDetails(Request $request) {
        $user_id = $request->header('userID');

        $customerDetails = Customer::where('user_id', $user_id)
            ->where('id', $request->input('cus_id'))
            ->first();

        $invoiceTotal = Invoice::where('user_id', $user_id)
            ->where('id', $request->input('inv_id'))
            ->first();

        $invoiceProduct = InvoiceProduct::where('invoice_id', $request->input('inv_id'))
            ->where('user_id', $user_id)
            ->with('product')
            ->get();

        return [
            'customer' => $customerDetails,
            'invoice'  => $invoiceTotal,
            'product'  => $invoiceProduct,
        ];
    }

    /**
     * Delete an invoice and put its stock back.
     *
     * The original deleted by id alone -- `Invoice::where('id', $inv_id)` with
     * no owner in the clause -- so any signed-in user could delete anybody's
     * invoice by guessing a number. The child rows were scoped and the parent
     * was not, which is the worst of both: the ownership check was written, it
     * just was not applied where it mattered.
     */
    function invoiceDelete(Request $request): JsonResponse {
        $user_id = $request->header('userID');

        DB::beginTransaction();
        try {
            $invoice = Invoice::where('id', $request->input('inv_id'))
                ->where('user_id', $user_id)
                ->first();

            if (!$invoice) {
                DB::rollBack();
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Invoice not found.',
                ], 404);
            }

            $soldLines = InvoiceProduct::where('invoice_id', $invoice->id)
                ->where('user_id', $user_id)
                ->get();

            foreach ($soldLines as $line) {
                $product = Product::where('id', $line->product_id)
                    ->where('user_id', $user_id)
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    continue;
                }

                $qty = (int) $line->qty;
                $balanceAfter = $product->stock + $qty;
                $product->increment('stock', $qty);

                // The invoice_id is left null because the invoice row is about
                // to go; the note keeps the reference readable afterwards.
                StockMovement::create([
                    'user_id'       => $user_id,
                    'product_id'    => $product->id,
                    'invoice_id'    => null,
                    'reason'        => 'sale_reversal',
                    'quantity'      => $qty,
                    'balance_after' => $balanceAfter,
                    'note'          => "Invoice #{$invoice->id} deleted",
                ]);
            }

            InvoiceProduct::where('invoice_id', $invoice->id)
                ->where('user_id', $user_id)
                ->delete();

            $invoice->delete();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Invoice deleted and stock restored.',
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'failed',
                'message' => 'Could not delete the invoice.',
            ], 500);
        }
    }
}
