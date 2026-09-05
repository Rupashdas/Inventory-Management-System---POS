<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller {

    function categoryPage() {
        return view('pages.dashboard.category-page');
    }

    function categoryList(Request $request) {
        $user_id = $request->header('userID');

        // withCount, not a query per row: the page shows how many products sit
        // in each category, and building that in the loop would be one query
        // per category.
        return Category::where('user_id', $user_id)
            ->withCount('products')
            ->orderBy('name')
            ->get();
    }

    private function validateCategory(Request $request): ?string {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
        ]);

        return $validator->fails() ? $validator->errors()->first() : null;
    }

    function categoryCreate(Request $request): JsonResponse {
        $user_id = (int) $request->header('userID');

        if ($error = $this->validateCategory($request)) {
            return response()->json(['status' => 'failed', 'message' => $error], 422);
        }

        $name = trim($request->input('name'));

        // Unique per user rather than globally: two shops may both keep a
        // "Beverages", but one shop keeping two of them is a mistake.
        $exists = Category::where('user_id', $user_id)->where('name', $name)->exists();
        if ($exists) {
            return response()->json([
                'status'  => 'failed',
                'message' => "You already have a category called \"{$name}\".",
            ], 422);
        }

        $category = Category::create(['name' => $name, 'user_id' => $user_id]);

        return response()->json(['status' => 'success', 'message' => 'Category added.', 'data' => $category], 201);
    }

    function categoryDelete(Request $request): JsonResponse {
        $user_id = (int) $request->header('userID');

        $category = Category::where('id', $request->input('id'))
            ->where('user_id', $user_id)
            ->first();

        if (!$category) {
            return response()->json(['status' => 'failed', 'message' => 'Category not found.'], 404);
        }

        // The products table restricts deletes on this key, so without the
        // check the request came back as a database error rather than an
        // explanation.
        $inUse = DB::table('products')->where('category_id', $category->id)->count();
        if ($inUse) {
            return response()->json([
                'status'  => 'failed',
                'message' => "{$inUse} product" . ($inUse === 1 ? '' : 's') . " use this category. Move them first.",
            ], 409);
        }

        $category->delete();

        return response()->json(['status' => 'success', 'message' => 'Category deleted.']);
    }

    function categoryByID(Request $request) {
        $user_id = $request->header('userID');
        return Category::where('id', $request->input('id'))
            ->where('user_id', $user_id)
            ->first();
    }

    function categoryUpdate(Request $request): JsonResponse {
        $user_id = (int) $request->header('userID');

        $category = Category::where('id', $request->input('id'))
            ->where('user_id', $user_id)
            ->first();

        if (!$category) {
            return response()->json(['status' => 'failed', 'message' => 'Category not found.'], 404);
        }

        if ($error = $this->validateCategory($request)) {
            return response()->json(['status' => 'failed', 'message' => $error], 422);
        }

        $category->update(['name' => trim($request->input('name'))]);

        return response()->json(['status' => 'success', 'message' => 'Category updated.']);
    }
}
