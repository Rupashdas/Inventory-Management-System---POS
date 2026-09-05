<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CustomerController extends Controller {

    public function customerPage() {
        return view('pages.dashboard.customer-page');
    }

    /**
     * Emails are unique across the whole table, so the rule has to ignore the
     * row being edited -- otherwise saving a customer without changing their
     * address fails on a clash with themselves.
     *
     * Without any of this a duplicate address reached the database and came
     * back to the browser as a 500 with a stack trace where a sentence should
     * have been.
     */
    private function validateCustomer(Request $request, ?int $ignoreId = null): ?string {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:100',
            'email'  => ['required', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($ignoreId)],
            'mobile' => 'required|string|max:30',
        ], [
            'email.unique' => 'Another customer already has that email address.',
        ]);

        return $validator->fails() ? $validator->errors()->first() : null;
    }

    public function customerCreate(Request $request): JsonResponse {
        $user_id = (int) $request->header('userID');

        if ($error = $this->validateCustomer($request)) {
            return response()->json(['status' => 'failed', 'message' => $error], 422);
        }

        $customer = Customer::create([
            'name'    => $request->input('name'),
            'email'   => $request->input('email'),
            'mobile'  => $request->input('mobile'),
            'user_id' => $user_id,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Customer added.', 'data' => $customer], 201);
    }

    public function customerList(Request $request) {
        $user_id = $request->header('userID');
        return Customer::where('user_id', $user_id)->orderBy('name')->get();
    }

    public function customerDelete(Request $request): JsonResponse {
        $user_id = (int) $request->header('userID');

        $customer = Customer::where('user_id', $user_id)
            ->where('id', $request->input('id'))
            ->first();

        if (!$customer) {
            return response()->json(['status' => 'failed', 'message' => 'Customer not found.'], 404);
        }

        // Invoices carry a foreign key to the customer. Deleting one out from
        // under its own sales history is a cascade nobody asked for.
        $hasInvoices = DB::table('invoices')->where('customer_id', $customer->id)->exists();
        if ($hasInvoices) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'This customer has invoices and cannot be deleted.',
            ], 409);
        }

        $customer->delete();

        return response()->json(['status' => 'success', 'message' => 'Customer deleted.']);
    }

    public function customerByID(Request $request) {
        $user_id = $request->header('userID');
        return Customer::where('user_id', $user_id)
            ->where('id', $request->input('id'))
            ->first();
    }

    function customerUpdate(Request $request): JsonResponse {
        $user_id = (int) $request->header('userID');

        $customer = Customer::where('user_id', $user_id)
            ->where('id', $request->input('id'))
            ->first();

        if (!$customer) {
            return response()->json(['status' => 'failed', 'message' => 'Customer not found.'], 404);
        }

        if ($error = $this->validateCustomer($request, $customer->id)) {
            return response()->json(['status' => 'failed', 'message' => $error], 422);
        }

        $customer->update([
            'name'   => $request->input('name'),
            'email'  => $request->input('email'),
            'mobile' => $request->input('mobile'),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Customer updated.']);
    }
}
