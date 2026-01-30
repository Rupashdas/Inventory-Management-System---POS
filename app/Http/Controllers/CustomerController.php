<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller {
    public function customerPage() {
        return view('pages.dashboard.customer-page');
    }

    public function customerCreate(Request $request) {
        $user_id = $request->header('userID');
        return Customer::create([
            'name'    => $request->name,
            'email'   => $request->email,
            'mobile'  => $request->mobile,
            'user_id' => $user_id,
        ]);
    }

    public function customerList(Request $request) {
        $user_id = $request->header('userID');
        return Customer::where('user_id', $user_id)->get();
    }

    public function customerDelete(Request $request) {
        $user_id = $request->header('userID');
        $customer_id = $request->id;
        return Customer::where('user_id', $user_id)->where('id', $customer_id)->delete();
    }

    public function customerByID(Request $request) {
        $user_id = $request->header('userID');
        $customer_id = $request->id;
        return Customer::where('user_id', $user_id)->where('id', $customer_id)->first();
    }

    function customerUpdate(Request $request) {
        $user_id = $request->header('userID');
        $customer_id = $request->id;
        return Customer::where('user_id', $user_id)->where('id', $customer_id)->update([
            'name'   => $request->name,
            'email'  => $request->email,
            'mobile' => $request->mobile,
        ]);
    }
}
