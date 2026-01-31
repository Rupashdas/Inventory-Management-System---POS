<?php

namespace App\Http\Controllers;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceController extends Controller {
    /**
     * Controller for handling invoice-related pages and API actions.
     *
     * Methods return views for pages or perform CRUD operations on
     * Invoice and InvoiceProduct models. Database transactions are used
     * where multiple related records are created or deleted.
     */

    /**
     * Show the invoice page view.
     *
     * @return View
     */
    function InvoicePage(): View {
        return view('pages.dashboard.invoice-page');
    }

    /**
     * Show the sale page view.
     *
     * @return View
     */
    function SalePage(): View {
        return view('pages.dashboard.sale-page');
    }

    /**
     * Create a new invoice and its related invoice products.
     * Expects request with headers: 'userID' and body fields: total, discount,
     * vat, payable, customer_id and products (array of product entries).
     * Uses a DB transaction to ensure atomicity.
     *
     * @param Request $request
     * @return int 1 on success, 0 on failure
     */
    function invoiceCreate(Request $request) {

        // Begin a DB transaction so that invoice and invoice products
        // are created atomically.
        DB::beginTransaction();

        try {

            $user_id = $request->header('userID');
            $total = $request->input('total');
            $discount = $request->input('discount');
            $vat = $request->input('vat');
            $payable = $request->input('payable');

            $customer_id = $request->input('customer_id');

            // Create the invoice record
            $invoice = Invoice::create([
                'total'       => $total,
                'discount'    => $discount,
                'vat'         => $vat,
                'payable'     => $payable,
                'user_id'     => $user_id,
                'customer_id' => $customer_id,
            ]);

            $invoiceID = $invoice->id;

            $products = $request->input('products');

            // Create each invoice product linked to the invoice
            foreach ($products as $EachProduct) {
                InvoiceProduct::create([
                    'invoice_id' => $invoiceID,
                    'user_id'    => $user_id,
                    'product_id' => $EachProduct['product_id'],
                    'qty'        => $EachProduct['qty'],
                    'sale_price' => $EachProduct['sale_price'],
                ]);
            }

            DB::commit();

            return 1;

        } catch (Exception $e) {
            // Roll back on any error and return failure marker
            DB::rollBack();
            return 0;
        }

    }

    /**
     * Select and return all invoices for the current user along with
     * their related customer data.
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function invoiceSelect(Request $request) {
        $user_id = $request->header('userID');
        return Invoice::where('user_id', $user_id)->with('customer')->get();
    }

    /**
     * Return detailed information for a specific invoice: the customer,
     * invoice summary, and invoice products with product relation.
     *
     * Expects request inputs: 'cus_id' and 'inv_id'.
     *
     * @param Request $request
     * @return array
     */
    function InvoiceDetails(Request $request) {
        $user_id = $request->header('userID');
        $customerDetails = Customer::where('user_id', $user_id)->where('id', $request->input('cus_id'))->first();
        $invoiceTotal = Invoice::where('user_id', '=', $user_id)->where('id', $request->input('inv_id'))->first();
        $invoiceProduct = InvoiceProduct::where('invoice_id', $request->input('inv_id'))->where('user_id', $user_id)->with('product')->get();
        return array(
            'customer' => $customerDetails,
            'invoice'  => $invoiceTotal,
            'product'  => $invoiceProduct,
        );
    }

    /**
     * Delete an invoice and its related invoice products within a DB transaction.
     * Expects request input: 'inv_id' and header 'userID' to scope the deletion.
     *
     * @param Request $request
     * @return int 1 on success, 0 on failure
     */
    function invoiceDelete(Request $request) {
        DB::beginTransaction();
        try {
            $user_id = $request->header('userID');

            // Remove related invoice product rows first
            InvoiceProduct::where('invoice_id', $request->input('inv_id'))->where('user_id', $user_id)->delete();

            // Then remove the invoice record
            Invoice::where('id', $request->input('inv_id'))->delete();
            DB::commit();
            return 1;
        } catch (Exception $e) {
            DB::rollBack();
            return 0;
        }
    }
}
