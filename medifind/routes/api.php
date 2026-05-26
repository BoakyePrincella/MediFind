<?php


use App\Http\Controllers\Api\V1\Public\ProductSearchController;
use App\Http\Controllers\Api\V1\Public\ShopProfileController;
use App\Http\Controllers\Api\V1\Shop\ShopDashboardController;
use App\Http\Controllers\Api\V1\Shop\ShopInventoryController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\ProductController;
use App\Http\Controllers\Api\V1\Admin\ShopController;
use App\Http\Controllers\Api\V1\Admin\ShopProductController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Auth Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {

        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);

        Route::middleware(['auth:sanctum'])->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });


    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {

        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('shops', ShopController::class);
        Route::patch('shops/{shop}/verify',[ShopController::class, 'verify']);

            // Shop products
        Route::get('shops/{shop}/products',[ShopProductController::class, 'index']);
        Route::post('shops/{shop}/products',[ShopProductController::class, 'store']);

        Route::put('shops/{shop}/products/{shopProduct}', [ShopProductController::class, 'update']);

        Route::delete('shops/{shop}/products/{shopProduct}',[ShopProductController::class, 'destroy'] );
        });


    /*
    |--------------------------------------------------------------------------
    | Shop Owner Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'role:shop_owner'])->prefix('shop')->group(function () {

            // Shop profile
        Route::get('profile', [ShopDashboardController::class, 'show']);
         Route::put('profile', [ShopDashboardController::class, 'update']);

            // Inventory
        Route::get('inventory',[ShopInventoryController::class, 'index']);
        Route::post('inventory/products',[ShopInventoryController::class, 'storeProduct']);
        Route::post('inventory',[ShopInventoryController::class, 'store']);
        Route::put('inventory/{shopProduct}',[ShopInventoryController::class, 'update']);
        Route::delete('inventory/{shopProduct}',[ShopInventoryController::class, 'destroy']);
        });


    /*
    |--------------------------------------------------------------------------
    | Public Routes
    |--------------------------------------------------------------------------
    */
    Route::get('categories',[ProductSearchController::class, 'categories']);
    Route::get('products',[ProductSearchController::class, 'search']);
    Route::get('products/{slug}', [ProductSearchController::class, 'show']);

    Route::get('shops',[ShopProfileController::class, 'index']);
    Route::get('shops/{slug}',[ShopProfileController::class, 'show']
    );
});
