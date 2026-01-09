<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\RolesPermissionsController;
use App\Http\Controllers\Admin\KdsPageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\ShoppingListController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\PublicSiteController;

Route::get('/', [PublicSiteController::class, 'index'])->name('home');
Route::post('/reservations', [PublicSiteController::class, 'storeReservation'])->name('public.reservations.store');

use App\Http\Controllers\Mobile\ShoppingListController as MobileShoppingListController;

// Mobile Routes (Dedicated View)
Route::middleware(['web'])->prefix('mobile')->name('mobile.')->group(function () {
    Route::get('/shopping-list', [MobileShoppingListController::class, 'index'])->name('shopping_list');
    Route::post('/shopping-list/toggle/{item}', [MobileShoppingListController::class, 'toggle'])->name('shopping_list.toggle');
    Route::post('/shopping-list/update/{item}', [MobileShoppingListController::class, 'updateQuantity'])->name('shopping_list.update');
    Route::post('/shopping-list/confirm', [MobileShoppingListController::class, 'confirm'])->name('shopping_list.confirm');
    Route::post('/shopping-list/add', [MobileShoppingListController::class, 'store'])->name('shopping_list.store');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    // Backups
    Route::post('/settings/backup', [SettingsController::class, 'createBackup'])->name('settings.backup.create');
    Route::get('/settings/backup/{filename}', [SettingsController::class, 'downloadBackup'])->name('settings.backup.download');
    Route::post('/settings/backup/{filename}/restore', [SettingsController::class, 'restoreBackup'])->name('settings.backup.restore');

    Route::get('/users', [UsersController::class, 'index'])->name('users');
    Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
    Route::post('/users', [UsersController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/roles', [UsersController::class, 'syncRoles'])->name('users.roles.sync');
    
    // Categories & Products
    Route::post('/categories/auto-detect', [CategoryController::class, 'autoDetectDrinks'])->name('categories.auto_detect');
    Route::resource('categories', CategoryController::class);
    Route::post('/products/{product}/recipe', [ProductController::class, 'updateRecipe'])->name('products.recipe.update');
    Route::resource('products', ProductController::class);
    Route::resource('product-options', \App\Http\Controllers\Admin\ProductOptionGroupController::class);
    
    // Inventory & Suppliers
    Route::get('/inventory/audit', [InventoryController::class, 'audit'])->name('inventory.audit');
    Route::post('/inventory/audit', [InventoryController::class, 'updateAudit'])->name('inventory.audit.update');
    Route::resource('inventory', InventoryController::class);
    Route::resource('suppliers', SupplierController::class);

    // Shopping List
    Route::get('/shopping-list', [ShoppingListController::class, 'index'])->name('shopping_list');
    Route::post('/shopping-list/store', [ShoppingListController::class, 'store'])->name('shopping_list.store');
    Route::post('/shopping-list/sync', [ShoppingListController::class, 'syncStock'])->name('shopping_list.sync');
    Route::post('/shopping-list/clear', [ShoppingListController::class, 'clear'])->name('shopping_list.clear');
    Route::post('/shopping-list/{item}/toggle', [ShoppingListController::class, 'toggleCheck'])->name('shopping_list.toggle');

    Route::get('/roles-permissions', [RolesPermissionsController::class, 'index'])->name('roles_permissions');
    Route::post('/roles/{role}/permissions', [RolesPermissionsController::class, 'syncRolePermissions'])->name('roles.permissions.sync');
    Route::get('/kds', [KdsPageController::class, 'index'])->name('kds');
    Route::resource('qr-codes', \App\Http\Controllers\Admin\QrCodeController::class)->only(['index', 'store', 'destroy']);

    // Floors & Tables
    Route::resource('floors', \App\Http\Controllers\Admin\FloorController::class);
    Route::post('/floors/{floor}/update-tables', [\App\Http\Controllers\Admin\FloorController::class, 'updateTables'])->name('floors.update_tables');

    // Reservations
    Route::resource('reservations', \App\Http\Controllers\Admin\ReservationController::class);
});


Route::middleware(['auth'])->prefix('restaurant')->name('restaurant.')->group(function () {
    Route::get('/', [\App\Http\Controllers\RestaurantController::class, 'index'])->name('index');

    // POS Routes
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Waiter\WaiterController::class, 'index'])->name('index');
        Route::post('/order', [\App\Http\Controllers\Waiter\WaiterController::class, 'storeOrder'])->name('order.store');
        Route::get('/session/{tableId}', [\App\Http\Controllers\Waiter\WaiterController::class, 'getSessionDetails'])->name('session.details');
        Route::get('/session/{sessionId}/print', [\App\Http\Controllers\Waiter\WaiterController::class, 'printBill'])->name('session.print');
        Route::post('/session/{sessionId}/close', [\App\Http\Controllers\Waiter\WaiterController::class, 'closeSession'])->name('session.close');
        Route::get('/ready-items', [\App\Http\Controllers\Waiter\WaiterController::class, 'getReadyItems'])->name('ready-items');
        Route::delete('/item/{id}', [\App\Http\Controllers\Waiter\WaiterController::class, 'deleteItem'])->name('item.delete');
        Route::get('/live-items', [\App\Http\Controllers\Waiter\WaiterController::class, 'getLiveItems'])->name('live-items');
        Route::post('/check-qr', [\App\Http\Controllers\Waiter\WaiterController::class, 'checkQr'])->name('check-qr');
        Route::post('/assign-table', [\App\Http\Controllers\Waiter\WaiterController::class, 'assignTable'])->name('assign-table');
        Route::get('/active-sessions', [\App\Http\Controllers\Waiter\WaiterController::class, 'getActiveSessions'])->name('active-sessions');
        Route::post('/mark-served/{itemId}', [\App\Http\Controllers\Waiter\WaiterController::class, 'markAsServed'])->name('mark-served');
    });
});

Route::middleware(['auth'])->prefix('kds')->name('kds.')->group(function () {
    Route::get('/kitchen', [\App\Http\Controllers\KdsController::class, 'index'])->name('kitchen');
    Route::get('/bar', [\App\Http\Controllers\KdsController::class, 'bar'])->name('bar');
    Route::get('/items', [\App\Http\Controllers\KdsController::class, 'getItems'])->name('items');
    Route::post('/item/{id}/status', [\App\Http\Controllers\KdsController::class, 'updateStatus'])->name('update-status');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Customer Menu Routes
Route::prefix('menu')->name('menu.')->group(function () {
    Route::get('/table/{tableId}', [\App\Http\Controllers\Customer\MenuController::class, 'tableIndex'])->name('table');
    Route::get('/online', [\App\Http\Controllers\Customer\MenuController::class, 'onlineIndex'])->name('online');
    Route::post('/order', [\App\Http\Controllers\Customer\MenuController::class, 'storeOrder'])->name('order.store');
});
