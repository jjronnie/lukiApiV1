<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\GoogleAuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordController;
use App\Http\Controllers\Api\V1\Catalog\ServiceCatalogController;
use App\Http\Controllers\Api\V1\Dispute\DisputeController;
use App\Http\Controllers\Api\V1\Order\OrderRatingController;
use App\Http\Controllers\Api\V1\Order\UserOrderController;
use App\Http\Controllers\Api\V1\Pricing\PriceEstimateController;
use App\Http\Controllers\Api\V1\Provider\ProviderAvailabilityController;
use App\Http\Controllers\Api\V1\Provider\ProviderDocumentController;
use App\Http\Controllers\Api\V1\Provider\ProviderOfferController;
use App\Http\Controllers\Api\V1\Provider\ProviderOrderController;
use App\Http\Controllers\Api\V1\Provider\ProviderProfileController;
use App\Http\Controllers\Api\V1\Provider\ProviderServiceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth-api');
        Route::post('/register/verify', [AuthController::class, 'verifyEmailOtp'])->middleware('throttle:auth-api');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth-api');
        Route::post('/login/verify', [AuthController::class, 'verifyLoginOtp'])->middleware('throttle:auth-api');
        Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('throttle:auth-api');
        Route::post('/password/forgot', [PasswordController::class, 'forgot'])->middleware('throttle:auth-api');
        Route::post('/password/reset', [PasswordController::class, 'reset'])->middleware('throttle:auth-api');
        Route::post('/google', [GoogleAuthController::class, 'login'])->middleware('throttle:auth-api');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/password/change', [PasswordController::class, 'change']);
            Route::post('/email/verify/send', [EmailVerificationController::class, 'send'])->middleware('throttle:auth-api');
        });
    });

    Route::get('/services', [ServiceCatalogController::class, 'index']);
    Route::get('/services/{public_id}', [ServiceCatalogController::class, 'show']);
    Route::get('/services/{public_id}/addons', [ServiceCatalogController::class, 'addons']);
    Route::post('/price/estimate', PriceEstimateController::class);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('provider')->group(function () {
            Route::post('/profile', [ProviderProfileController::class, 'upsert'])->middleware('role:user|provider|admin|superadmin');

            Route::middleware('role:provider|admin|superadmin')->group(function () {
                Route::post('/documents', [ProviderDocumentController::class, 'store']);
                Route::post('/services', [ProviderServiceController::class, 'sync']);
                Route::post('/availability/online', [ProviderAvailabilityController::class, 'online']);
                Route::post('/availability/offline', [ProviderAvailabilityController::class, 'offline']);
                Route::post('/heartbeat', [ProviderAvailabilityController::class, 'heartbeat']);
                Route::get('/offers', [ProviderOfferController::class, 'index']);
                Route::post('/offers/{order_public_id}/accept', [ProviderOfferController::class, 'accept']);
                Route::post('/orders/{order_public_id}/status', [ProviderOrderController::class, 'updateStatus']);
            });
        });

        Route::middleware('role:user|admin|superadmin')->group(function () {
            Route::post('/orders', [UserOrderController::class, 'store']);
            Route::get('/orders', [UserOrderController::class, 'index']);
            Route::get('/orders/{public_id}', [UserOrderController::class, 'show']);
            Route::post('/orders/{public_id}/cancel', [UserOrderController::class, 'cancel']);
            Route::post('/orders/{public_id}/rate', [OrderRatingController::class, 'store']);
            Route::post('/disputes', [DisputeController::class, 'store']);
        });
    });
});
