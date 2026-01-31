<?php
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\TokenVerificationMiddleware;
use Illuminate\Support\Facades\Route;

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

Route::get('/categoryPage', [CategoryController::class, 'categoryPage'])->middleware([TokenVerificationMiddleware::class]);

// Category API
Route::post("/create-category", [CategoryController::class, 'categoryCreate'])->middleware([TokenVerificationMiddleware::class]);
Route::get("/list-category", [CategoryController::class, 'categoryList'])->middleware([TokenVerificationMiddleware::class]);
Route::post("/delete-category", [CategoryController::class, 'categoryDelete'])->middleware([TokenVerificationMiddleware::class]);
Route::post("/update-category", [CategoryController::class, 'categoryUpdate'])->middleware([TokenVerificationMiddleware::class]);
Route::post("/category-by-id", [CategoryController::class, 'categoryByID'])->middleware([TokenVerificationMiddleware::class]);

Route::get('/customerPage', [CustomerController::class, 'customerPage'])->middleware([TokenVerificationMiddleware::class]);

// Customer API
Route::post("/create-customer", [CustomerController::class, 'customerCreate'])->middleware([TokenVerificationMiddleware::class]);
Route::get("/list-customer", [CustomerController::class, 'customerList'])->middleware([TokenVerificationMiddleware::class]);
Route::post("/delete-customer", [CustomerController::class, 'customerDelete'])->middleware([TokenVerificationMiddleware::class]);
Route::post("/update-customer", [CustomerController::class, 'customerUpdate'])->middleware([TokenVerificationMiddleware::class]);
Route::post("/customer-by-id", [CustomerController::class, 'customerByID'])->middleware([TokenVerificationMiddleware::class]);

Route::get("/productPage", [ProductController::class, "productPage"])->middleware([TokenVerificationMiddleware::class]);
Route::get('/invoicePage', [InvoiceController::class, 'InvoicePage'])->middleware([TokenVerificationMiddleware::class]);
Route::get('/salePage', [InvoiceController::class, 'SalePage'])->middleware([TokenVerificationMiddleware::class]);

// Product API
Route::post("/create-product", [ProductController::class, 'createProduct'])->middleware([TokenVerificationMiddleware::class]);
Route::post("/delete-product", [ProductController::class, 'deleteProduct'])->middleware([TokenVerificationMiddleware::class]);
Route::post("/update-product", [ProductController::class, 'updateProduct'])->middleware([TokenVerificationMiddleware::class]);
Route::get("/list-product", [ProductController::class, 'productList'])->middleware([TokenVerificationMiddleware::class]);
Route::post("/product-by-id", [ProductController::class, 'productByID'])->middleware([TokenVerificationMiddleware::class]);

// Invoice
Route::post("/invoice-create", [InvoiceController::class, 'invoiceCreate'])->middleware([TokenVerificationMiddleware::class]);
Route::get("/invoice-select", [InvoiceController::class, 'invoiceSelect'])->middleware([TokenVerificationMiddleware::class]);
Route::post("/invoice-details", [InvoiceController::class, 'InvoiceDetails'])->middleware([TokenVerificationMiddleware::class]);
Route::post("/invoice-delete", [InvoiceController::class, 'invoiceDelete'])->middleware([TokenVerificationMiddleware::class]);