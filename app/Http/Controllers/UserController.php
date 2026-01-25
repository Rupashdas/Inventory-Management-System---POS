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

    public function loginPage(){
        return view('pages.auth.login-page');
    }
    public function registrationPage(){
        return view('pages.auth.registration-page');
    }
    public function sendOtpPage(){
        return view('pages.auth.send-otp-page');
    }
    public function verifyOtpPage(){
        return view('pages.auth.verify-otp-page');
    }
    public function resetPasswordPage(){
        return view('pages.auth.reset-pass-page');
    }

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

            $email = $request->input('email');
            $password = $request->input('password');
            $user = User::where('email', $email)->first();
            $count = 0;
            
            if ($user && Hash::check($password, $user->password)) {
                $count = 1;
            }

            if($count == 1){
                $token = JWTToken::createToken($email);
                return response()->json([
                    'message' => 'Login successful',
                    'status' => "success",
                ], 200)->cookie('pos_token', $token, 60*24);
            }else{
                return response()->json([
                    'message' => 'Email or Password is incorrect',
                    'status' => "failed",
                ], 401);
            }


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
        $user->otp = $otp;
        $user->save();

        return response()->json([
            'message' => 'OTP sent successfully',
            'status' => "success",
        ], 200);
    }

    function verifyOTPCode(Request $request){
        $email = $request->input('email');
        $otp = $request->input('otp');
        $count = User::where('email', $email)->where('otp', $otp)->count();
        if(1 === $count){
            // Database OTP reset
            $user = User::where('email', $email)->first();
            $user->otp = "0";
            $user->save();
            $token = JWTToken::createTokenForSetPassword($email);
            return response()->json([
                'message' => 'OTP verified successfully',
                'status' => "success",
            ], 200)->cookie('reset_token', $token, 60*24*30);
        }else{
            return response()->json([
                'message' => 'Invalid OTP',
                'status' => "failed",
            ], 401);
        }
    }

    public function resetPassword(Request $request){
        try{
            $email = $request->header('userEmail');
            $newPassword = $request->input('newPassword');
            $user = User::where('email', $email)->first();
            if(!$user){
                return response()->json([
                    'message' => 'User not found',
                    'status' => "failed",
                ], 404);
            }
            $user->password = Hash::make($newPassword);
            $user->save();

            return response()->json([
                'message' => 'Password reset successfully',
                'status' => "success",
            ], 200);
        }catch(Exception $e){
            return response()->json([
                'message' => 'Password reset failed',
                'status' => "failed",
            ], 500);
        }
        
    }


}
