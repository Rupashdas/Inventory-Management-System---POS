<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller {

    public function productPage() {
        return view("pages.dashboard.product-page");
    }

    /**
     * Remove a product's image from disk.
     *
     * The path comes from the product row, never from the request. The original
     * did the opposite -- it read `file_path` out of the POST body and handed it
     * straight to File::delete() -- which meant the request got to choose which
     * file on the server disappeared. Deriving it here, and confirming the
     * resolved path really sits inside public/uploads, closes that.
     */
    private function deleteImage(?string $imgUrl): void {
        if (!$imgUrl) {
            return;
        }

        $uploads = realpath(public_path('uploads'));
        $target  = realpath(public_path($imgUrl));

        if (!$uploads || !$target) {
            return;
        }

        if (str_starts_with($target, $uploads . DIRECTORY_SEPARATOR)) {
            File::delete($target);
        }
    }

    /**
     * Store an uploaded image under a name we choose.
     *
     * getClientOriginalName() is attacker-controlled text and it used to be
     * pasted into the filename as-is. A generated name keeps traversal
     * sequences and odd extensions out of the path entirely.
     */
    private function storeImage(Request $request, int $userId): string {
        $img = $request->file('img');
        $name = $userId . '-' . time() . '-' . Str::random(8) . '.' . $img->extension();
        $img->move(public_path('uploads'), $name);

        return "uploads/{$name}";
    }

    private function validateProduct(Request $request, bool $requireImage): array {
        return Validator::make($request->all(), [
            'name'                => 'required|string|max:100',
            'price'               => 'required|numeric|min:0',
            'unit'                => 'required|string|max:50',
            'category_id'         => 'required|integer',
            'stock'               => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'img'                 => ($requireImage ? 'required' : 'nullable') . '|image|mimes:jpg,jpeg,png,webp|max:2048',
        ])->errors()->all();
    }

    public function createProduct(Request $request): JsonResponse {
        $user_id = (int) $request->header('userID');

        $errors = $this->validateProduct($request, true);
        if ($errors) {
            return response()->json(['status' => 'failed', 'message' => $errors[0]], 422);
        }

        // A product may only be filed under a category this user owns.
        $category = Category::where('user_id', $user_id)
            ->where('id', $request->input('category_id'))
            ->first();

        if (!$category) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Choose a category from your own list.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $openingStock = (int) $request->input('stock', 0);

            $product = Product::create([
                'user_id'             => $user_id,
                'category_id'         => $category->id,
                'name'                => $request->input('name'),
                'price'               => $request->input('price'),
                'unit'                => $request->input('unit'),
                'stock'               => $openingStock,
                'low_stock_threshold' => (int) $request->input('low_stock_threshold', 5),
                'img_url'             => $this->storeImage($request, $user_id),
            ]);

            // Opening stock is a movement like any other, so the ledger starts
            // at the same place the balance does.
            if ($openingStock > 0) {
                StockMovement::create([
                    'user_id'       => $user_id,
                    'product_id'    => $product->id,
                    'reason'        => 'purchase',
                    'quantity'      => $openingStock,
                    'balance_after' => $openingStock,
                    'note'          => 'Opening stock',
                ]);
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Product created.', 'data' => $product], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'failed', 'message' => 'Could not create the product.'], 500);
        }
    }

    public function deleteProduct(Request $request): JsonResponse {
        $user_id = (int) $request->header('userID');

        $product = Product::where('id', $request->input('id'))
            ->where('user_id', $user_id)
            ->first();

        if (!$product) {
            return response()->json(['status' => 'failed', 'message' => 'Product not found.'], 404);
        }

        // A product that has been sold is referenced by invoice lines; deleting
        // it would either fail on the foreign key or take sales history with
        // it. Neither is what a shopkeeper means by "remove this product".
        $sold = DB::table('invoice_products')->where('product_id', $product->id)->exists();
        if ($sold) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'This product appears on existing invoices and cannot be deleted.',
            ], 409);
        }

        $imgUrl = $product->img_url;
        $product->delete();
        $this->deleteImage($imgUrl);

        return response()->json(['status' => 'success', 'message' => 'Product deleted.']);
    }

    function ProductByID(Request $request) {
        $user_id = $request->header('userID');
        return Product::where('id', $request->input('id'))
            ->where('user_id', $user_id)
            ->first();
    }

    function ProductList(Request $request) {
        $user_id = $request->header('userID');
        return Product::where('user_id', $user_id)
            ->with('category:id,name')
            ->orderBy('name')
            ->get();
    }

    function UpdateProduct(Request $request): JsonResponse {
        $user_id = (int) $request->header('userID');

        $product = Product::where('id', $request->input('id'))
            ->where('user_id', $user_id)
            ->first();

        if (!$product) {
            return response()->json(['status' => 'failed', 'message' => 'Product not found.'], 404);
        }

        $errors = $this->validateProduct($request, false);
        if ($errors) {
            return response()->json(['status' => 'failed', 'message' => $errors[0]], 422);
        }

        $category = Category::where('user_id', $user_id)
            ->where('id', $request->input('category_id'))
            ->first();

        if (!$category) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Choose a category from your own list.',
            ], 422);
        }

        $fields = [
            'name'                => $request->input('name'),
            'price'               => $request->input('price'),
            'unit'                => $request->input('unit'),
            'category_id'         => $category->id,
            'low_stock_threshold' => (int) $request->input('low_stock_threshold', $product->low_stock_threshold),
        ];

        // Stock is deliberately not editable here. It moves through restock and
        // through sales, both of which write a ledger row; letting this form
        // overwrite the balance would put the two permanently out of step.

        if ($request->hasFile('img')) {
            $oldImage = $product->img_url;
            $fields['img_url'] = $this->storeImage($request, $user_id);
            $product->update($fields);
            $this->deleteImage($oldImage);
        } else {
            $product->update($fields);
        }

        return response()->json(['status' => 'success', 'message' => 'Product updated.']);
    }

    /**
     * Put stock on the shelf.
     *
     * Separate from the edit form on purpose: restocking is an event with a
     * quantity and a reason, not a field you overwrite.
     */
    function restock(Request $request): JsonResponse {
        $user_id = (int) $request->header('userID');

        $validator = Validator::make($request->all(), [
            'id'       => 'required|integer',
            'quantity' => 'required|integer|min:1|max:100000',
            'note'     => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()], 422);
        }

        DB::beginTransaction();
        try {
            $product = Product::where('id', $request->input('id'))
                ->where('user_id', $user_id)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                DB::rollBack();
                return response()->json(['status' => 'failed', 'message' => 'Product not found.'], 404);
            }

            $quantity = (int) $request->input('quantity');
            $balanceAfter = $product->stock + $quantity;
            $product->increment('stock', $quantity);

            StockMovement::create([
                'user_id'       => $user_id,
                'product_id'    => $product->id,
                'reason'        => 'purchase',
                'quantity'      => $quantity,
                'balance_after' => $balanceAfter,
                'note'          => $request->input('note') ?: 'Restock',
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "{$quantity} added. {$product->name} is now at {$balanceAfter}.",
                'stock'   => $balanceAfter,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'failed', 'message' => 'Could not update stock.'], 500);
        }
    }

    /**
     * The movement history for one product, newest first.
     */
    function stockHistory(Request $request) {
        $user_id = $request->header('userID');

        $product = Product::where('id', $request->input('id'))
            ->where('user_id', $user_id)
            ->first();

        if (!$product) {
            return response()->json(['status' => 'failed', 'message' => 'Product not found.'], 404);
        }

        return [
            'product'   => $product,
            'movements' => StockMovement::where('user_id', $user_id)
                ->where('product_id', $product->id)
                ->orderByDesc('id')
                ->limit(50)
                ->get(),
        ];
    }
}
