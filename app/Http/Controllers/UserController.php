<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Exception;
use \App\Helper\JWTToken;
use Mail;
use App\Mail\OTPMail;

class UserController extends Controller
{

    /*
    * User registration
    */
    function userRegistration(Request $request){
        try{
            // Validation rules
            $validator = Validator::make($request->all(), [
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'mobile' => 'required|string|max:15|unique:users',
                'password' => 'required|string|min:6',
            ]);

            // Check validation failures
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validated data
            $validated = $validator->validated();
            
            // Create user
            $user = User::create([
                'firstName' => $validated['firstName'],
                'lastName' => $validated['lastName'],
                'email' => $validated['email'],
                'mobile' => $validated['mobile'],
                'password' => Hash::make($validated['password']),
            ]);

            // Return success response
            return response()->json([
                'message' => 'User registered successfully',
                'status' => "success",
            ], 201);
        } catch(Exception $e){
            // log error message
            \Log::error('User Registration Error: '.$e->getMessage());
            return response()->json([
                'message' => 'Registration failed',
                'status' => "failed",
            ], 500);

        }
    }

    
    /*
    * User Login
    */
    public function userLogin(Request $request){
        try{
            // // Validation rules
            // $validator = Validator::make($request->all(), [
            //     'email' => 'required|string|email',
            //     'password' => 'required|string',
            // ]);

            // // Check validation failures
            // if ($validator->fails()) {
            //     return response()->json([
            //         'errors' => $validator->errors()
            //     ], 422);
            // }

            // // Validated data
            // $validated = $validator->validated();

            // // Find user by email
            // $user = User::where('email', $validated['email'])->first();
            // if(!$user || !Hash::check($validated['password'], $user->password)){
            //     return response()->json([
            //         'message' => 'Invalid credentials',
            //         'status' => "failed",
            //     ], 401);
            // }

            // // Generate JWT Token
            // $token = JWTToken::createToken($user->email);

            // // Return success response with token
            // return response()->json([
            //     'message' => 'Login successful',
            //     'status' => "success",
            //     'token' => $token,
            // ], 200);

           
            $user = User::where('email', $request->input('email'))->first();
            if ($user && Hash::check($request->password, $user->password)) {
                $count = 1;
            } else {
                $count = 0;
            }

            if($count == 1){
                $token = JWTToken::createToken($request->input('email'));
                return response()->json([
                    'message' => 'Login successful',
                    'status' => "success",
                    'token' => $token,
                ], 200);
            }else{
                return response()->json([
                    'message' => 'Unauthorized access',
                    'status' => "failed",
                ], 401);
            }
        } catch(Exception $e){
            // log error message
            \Log::error('User Login Error: '.$e->getMessage());
            return response()->json([
                'message' => 'Login failed',
                'status' => "failed",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /*
    * Send OTP Code
    */
    public function sendOTPCode(Request $request){
        $email = $request->input('email');
        $otp = rand(1000, 9999);
        $user = User::where('email', $email)->first();
        if(!$user){
            return response()->json([
                'message' => 'Email not found',
                'status' => "failed",
            ], 404);
        }
        $fullName = $user->firstName . ' ' . $user->lastName;
        Mail::to($email)->send(new OTPMail($fullName, $otp));
        // update OTP in users table
        $user->otp = $otp;
        $user->save();

        return response()->json([
            'message' => 'OTP sent successfully',
            'status' => "success",
        ], 200);
    }


}
