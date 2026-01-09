<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QrController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\KdsController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/qr/{token}/status', [QrController::class, 'status']);
Route::post('/qr/activate', [QrController::class, 'activate']);

Route::post('/orders/{session}/add-item', [OrderController::class, 'addItem']);
Route::post('/orders/{order}/ready', [OrderController::class, 'markReady']);
Route::post('/orders/{order}/claim', [OrderController::class, 'claim']);
Route::post('/orders/{order}/served', [OrderController::class, 'served']);

Route::get('/kds/kitchen-queue', [KdsController::class, 'kitchenQueue']);
Route::get('/kds/bar-queue', [KdsController::class, 'barQueue']);
Route::patch('/kds/items/{item}/status', [KdsController::class, 'updateStatus']);

Route::post('/checkout/{session}/close', [CheckoutController::class, 'close']);

Route::get('/notifications/{user}', [NotificationController::class, 'list']);
Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
