<?php

namespace App\Http\Controllers;

use App\Mail\OTPMail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mail;
use \App\Helper\JWTToken;

class UserController extends Controller
{

    public function loginPage()
    {
        return view('pages.auth.login-page');
    }

    public function registrationPage()
    {
        return view('pages.auth.registration-page');
    }

    public function sendOtpPage()
    {
        return view('pages.auth.send-otp-page');
    }

    public function verifyOtpPage()
    {
        return view('pages.auth.verify-otp-page');
    }

    public function resetPasswordPage()
    {
        return view('pages.auth.reset-pass-page');
    }

    public function profilePage()
    {
        return view('pages.dashboard.profile-page');
    }

    /*
     * User registration
     */
    public function userRegistration(Request $request)
    {
        try {
            // Validation rules
            $validator = Validator::make($request->all(), [
                'firstName' => 'required|string|max:255',
                'lastName'  => 'required|string|max:255',
                'email'     => 'required|string|email|max:255|unique:users',
                'mobile'    => 'required|string|max:15|unique:users',
                'password'  => 'required|string|min:6',
            ]);

            // Check validation failures
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Validated data
            $validated = $validator->validated();

            // Create user
            User::create([
                'firstName' => $validated['firstName'],
                'lastName'  => $validated['lastName'],
                'email'     => $validated['email'],
                'mobile'    => $validated['mobile'],
                'password'  => Hash::make($validated['password']),
            ]);

            // Return success response
            return response()->json([
                'message' => 'User registered successfully',
                'status'  => "success",
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'status'  => "failed",
            ], 500);

        }
    }

    /*
     * User Login
     */
    public function userLogin(Request $request)
    {
        try {
            $email = $request->input('email');
            $password = $request->input('password');

            $user = User::where('email', $email)->first();
            $count = 0;

            if ($user && Hash::check($password, $user->password)) {
                $count = 1;
            }

            if ($count == 1) {
                $token = JWTToken::createToken($email, $user->id);
                return response()->json([
                    'message' => 'Login successful',
                    'status'  => "success",
                ], 200)->cookie('token', $token, 60 * 24);
            } else {
                return response()->json([
                    'message' => 'Email or Password is incorrect',
                    'status'  => "failed",
                ], 401);
            }

        } catch (Exception $e) {
            // log error message
            return response()->json([
                'message' => 'Login failed',
                'status'  => "failed",
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /*
     * Send OTP Code
     */
    public function sendOTPCode(Request $request)
    {
        $email = $request->input('email');
        $otp = rand(1000, 9999);
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'Email not found',
                'status'  => "failed",
            ], 404);
        }
        $fullName = $user->firstName . ' ' . $user->lastName;
        Mail::to($email)->send(new OTPMail($fullName, $otp));
        $user->otp = $otp;
        $user->save();

        return response()->json([
            'message' => 'OTP sent successfully',
            'status'  => "success",
        ], 200);
    }

    public function verifyOTPCode(Request $request)
    {
        $email = $request->input('email');
        $otp = $request->input('otp');
        $count = User::where('email', $email)->where('otp', $otp)->count();
        if (1 === $count) {
            // Database OTP reset
            $user = User::where('email', $email)->first();
            $user->otp = "0";
            $user->save();
            $token = JWTToken::createTokenForSetPassword($email);
            return response()->json([
                'message' => 'OTP verified successfully',
                'status'  => "success",
            ], 200)->cookie('token', $token, 60 * 24 * 30);
        } else {
            return response()->json([
                'message' => 'Invalid OTP',
                'status'  => "failed",
            ], 401);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $email = $request->header('userEmail');
            $newPassword = $request->input('newPassword');
            $user = User::where('email', $email)->first();
            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                    'status'  => "failed",
                ], 404);
            }
            $user->password = Hash::make($newPassword);
            $user->save();

            return response()->json([
                'message' => 'Password reset successfully',
                'status'  => "success",
            ], 200)->cookie('token', '', -1);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Password reset failed',
                'status'  => "failed",
            ], 500);
        }

    }

    public function userLogout()
    {
        return redirect('/userLogin')->cookie('token', '', -1);
    }

    public function userProfile(Request $request)
    {
        try {
            $email = $request->header('userEmail');
            $user = User::where('email', '=', $email)->first();
            return response()->json([
                'message' => 'user get successfully',
                'status'  => 'success',
                'data'    => [
                    'firstName' => $user->firstName,
                    'lastName'  => $user->lastName,
                    'mobile'    => $user->mobile,
                    'email'     => $user->email,
                    'password'  => '',
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'User profile not found',
                'status'  => 'failed',
                'error'   => $e,
            ], 404);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $email = $request->header('userEmail');
            $firstName = $request->input('firstName');
            $lastName = $request->input('lastName');
            $mobile = $request->input('mobile');
            $user = User::where('email', '=', $email)->first();
            if (!$user) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'User not found',
                ], 404);
            }
            $user->firstName = $firstName;
            $user->lastName = $lastName;
            $user->mobile = $mobile;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->input('password'));
            }
            $user->save();
            return response()->json([
                'message' => "Request Successful",
                'status'  => 'success',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Something went wrong',
            ], 500);
        }
    }

}
