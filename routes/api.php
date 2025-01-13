<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DisbursementOrderController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiptOrderController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AdminTicketController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\RentClientController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\StockingRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// endpoint
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register-staff', [AuthController::class, 'createWarehouseStaff']);
Route::get('/warehouse-staff', [AuthController::class, 'indexStaff']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/warehouses', [WarehouseController::class, 'index']);
Route::get('/info-warehouses', [WarehouseController::class, 'getInfoWarehouses']);
Route::post('/warehouses', [WarehouseController::class, 'store']);
Route::post('/warehouses/{warehouse_id}/set-limitations', [WarehouseController::class, 'setLimitations']);
Route::get('/get-users-with-warehouses', [WarehouseController::class, 'getAllUsersWithWarehouses']);

// Category Routes
Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);

// Product Routes
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products', [ProductController::class, 'index']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);
Route::get('/products-warehouse', [ProductController::class, 'getProductsByWarehouse']);

Route::post('/disbursement', [DisbursementOrderController::class, 'store']);
Route::get('/disbursement-orders', [DisbursementOrderController::class, 'index']);

Route::post('/inventories',[InventoryController::class,'store']);
Route::post('/receipt-orders',[ReceiptOrderController::class,'store']);
Route::get('/receipt-orders',[ReceiptOrderController::class,'index']);
Route::get('/inventories',[InventoryController::class,'getInventoriesByWarehouse']);
Route::get('/warehouses/{id}/statistics', [WarehouseController::class, 'getStatistics']);
Route::get('/statistics-users-warehouses-count',[StatisticsController::class,'getStatistics']);

Route::get('/users', [UserController::class, 'index']);
Route::put('/users/{id}', [UserController::class, 'updateStatus']);

Route::post('/pay', [PaymentController::class, 'store']);

Route::post('/activate-user/{id}', [UserController::class, 'activateUser']);

Route::delete('/users/{id}', [UserController::class, 'destroy']);


Route::post('/vouchers', [PaymentController::class, 'generateVoucher']);
Route::get('/vouchers', [PaymentController::class, 'getVouchers']);
Route::get('/vouchers/validate/{code}', [PaymentController::class, 'validateVoucher']);

Route::get('/subscriptions', [PaymentController::class, 'index']);
Route::get('/subscriptions/profits', [PaymentController::class, 'getProfitsData']);

Route::get('/products/{product_id}', [ProductController::class, 'show']);
Route::put('/products/{id}', [ProductController::class, 'update']);




Route::post('/tickets', [TicketController::class, 'store']);
// Get all tickets for the logged-in user
Route::get('/tickets', [TicketController::class, 'index']);

Route::get('/admin/tickets', [AdminTicketController::class, 'index']);
// Respond to a ticket
Route::post('/admin/tickets/{ticket}/respond', [AdminTicketController::class, 'respond']);
Route::get('/packages', [PackageController::class, 'index']);


Route::get('/enterprise-warehouses', [WarehouseController::class, 'getEnterpriseWarehouses']);


Route::post('/register-client', [AuthController::class, 'registerClient']);
Route::post('/pay-client', [PaymentController::class, 'storeClientPay']);
Route::post('/rent-clients', [AuthController::class, 'storeRentClient']);
Route::post('/check-user', [AuthController::class, 'checkUser']);
Route::post('/rent-client/response', [RentClientController::class, 'handleRentResponse']);
Route::get('/notifications', [NotificationController::class, 'getNotifications']);
Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
Route::get('/wallets/{userId}', [WalletController::class, 'show']);
// routes/api.php
Route::get('/rent-requests/{warehouseId}', [RentClientController::class, 'getRentRequests']);

// routes/api.php
Route::put('/users/{userId}/status', [RentClientController::class, 'updateUserStatus']);
Route::put('/users/{userId}/payment-status', [RentClientController::class, 'updateUserPaymentStatus']);


// routes/api.php
// Route::post('/stocking-requests', [StockingRequestController::class, 'store']);
Route::put('/stocking-requests/{id}', [StockingRequestController::class, 'update']);
Route::get('/stocking-requests/{warehouseId}', [StockingRequestController::class, 'index']);
Route::post('/register-client-and-request-stocking', [UserController::class, 'registerClientAndRequestStocking']);
Route::get('/client-stocking-requests/{clientId}', [StockingRequestController::class, 'getClientStockingRequests']);

Route::put('/stocking-requests/{id}/accept', [StockingRequestController::class, 'acceptStockingRequest']);
Route::put('/stocking-requests/{id}/decline', [StockingRequestController::class, 'declineStockingRequest']);

Route::put('/stocking-requests/{id}/mark-as-paid', [StockingRequestController::class, 'markAsPaid']);
Route::post('/validate-voucher', [VoucherController::class, 'validateVoucher']);

Route::post('/assign-products', [ProductController::class, 'assignProducts']);