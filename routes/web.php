<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Middleware\TokenVerificationMiddleware;
use App\Http\Controllers\DashboardController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::post('/user-registration', [UserController::class, 'userRegistration']);
Route::post('/user-login', [UserController::class, 'userLogin']);
Route::post('/send-otp', [UserController::class, 'sendOTPCode']);
Route::post('/verify-otp', [UserController::class, 'verifyOTPCode']);
Route::post('/reset-password', [UserController::class, 'resetPassword'])->middleware([TokenVerificationMiddleware::class]);

// Page Routes
Route::get('/userLogin', [UserController::class, 'loginPage']);
Route::get('/userRegistration', [UserController::class, 'registrationPage']);
Route::get('/sendOtp', [UserController::class, 'sendOtpPage']);
Route::get('/verifyOtp', [UserController::class, 'verifyOtpPage']);
Route::get('/resetPassword', [UserController::class, 'resetPasswordPage']);
Route::get('/dashboard', [DashboardController::class, 'dashboardPage']);