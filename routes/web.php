<?php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\TokenVerificationMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;

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
Route::get('/user-profile', [UserController::class, 'userProfile'])->middleware([TokenVerificationMiddleware::class]);
Route::post('/user-update', [UserController::class, 'updateProfile'])->middleware([TokenVerificationMiddleware::class]);

Route::get('/logout', [UserController::class, 'userLogout']);

// Page Routes
Route::get('/userLogin', [UserController::class, 'loginPage']);
Route::get('/userRegistration', [UserController::class, 'registrationPage']);
Route::get('/sendOtp', [UserController::class, 'sendOtpPage']);
Route::get('/verifyOtp', [UserController::class, 'verifyOtpPage']);
Route::get('/resetPassword', [UserController::class, 'resetPasswordPage'])->middleware(TokenVerificationMiddleware::class);
Route::get('/dashboard', [DashboardController::class, 'dashboardPage'])->middleware(TokenVerificationMiddleware::class);
Route::get('/userProfile', [UserController::class, 'profilePage'])->middleware(TokenVerificationMiddleware::class);


Route::get('/categoryPage', [CategoryController::class, 'CategoryPage'])->middleware([TokenVerificationMiddleware::class]);

// Category API
Route::post("/create-category", [CategoryController::class, 'CategoryCreate'])->middleware([TokenVerificationMiddleware::class]);
Route::get("/list-category", [CategoryController::class, 'CategoryList'])->middleware([TokenVerificationMiddleware::class]);
Route::post("/delete-category", [CategoryController::class, 'CategoryDelete'])->middleware([TokenVerificationMiddleware::class]);
Route::post("/update-category", [CategoryController::class, 'CategoryUpdate'])->middleware([TokenVerificationMiddleware::class]);
Route::post("/category-by-id", [CategoryController::class, 'CategoryByID'])->middleware([TokenVerificationMiddleware::class]);
