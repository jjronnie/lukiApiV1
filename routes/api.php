<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\GoogleAuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordController;
use App\Http\Controllers\Api\V1\Catalog\HomeAdvertController;
use App\Http\Controllers\Api\V1\Catalog\ServiceCatalogController;
use App\Http\Controllers\Api\V1\Catalog\ServiceCategoryController;
use App\Http\Controllers\Api\V1\Dispute\DisputeController;
use App\Http\Controllers\Api\V1\Notification\DeviceTokenController;
use App\Http\Controllers\Api\V1\Notification\NotificationController;
use App\Http\Controllers\Api\V1\Order\OrderRatingController;
use App\Http\Controllers\Api\V1\Order\UserOrderController;
use App\Http\Controllers\Api\V1\Pricing\PriceEstimateController;
use App\Http\Controllers\Api\V1\Provider\ProviderAvailabilityController;
use App\Http\Controllers\Api\V1\Provider\ProviderDocumentController;
use App\Http\Controllers\Api\V1\Provider\ProviderOfferController;
use App\Http\Controllers\Api\V1\Provider\ProviderOrderController;
use App\Http\Controllers\Api\V1\Provider\ProviderProfileController;
use App\Http\Controllers\Api\V1\Provider\ProviderServiceController;
use App\Http\Controllers\Api\V1\Provider\ProviderVerificationController;
use App\Http\Controllers\Api\V1\User\CustomerProfileController;
use App\Http\Controllers\Api\V1\User\EmailPreferenceController;
use App\Http\Controllers\Api\V1\User\UserIdentityVerificationController;
use App\Http\Controllers\Api\V1\User\UserIdentityVerificationSessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth-api');
        Route::post('/register/verify', [AuthController::class, 'verifyEmailOtp'])->middleware('throttle:auth-api');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth-api');
        Route::post('/login/verify', [AuthController::class, 'verifyLoginOtp'])->middleware('throttle:auth-api');
        Route::post('/otp/resend', [AuthController::class, 'resendOtp'])->middleware('throttle:auth-api');
        Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('throttle:auth-api');
        Route::post('/password/forgot', [PasswordController::class, 'forgot'])->middleware('throttle:auth-api');
        Route::post('/password/verify', [PasswordController::class, 'verify'])->middleware('throttle:auth-api');
        Route::post('/password/reset', [PasswordController::class, 'reset'])->middleware('throttle:auth-api');
        Route::post('/google', [GoogleAuthController::class, 'login'])->middleware('throttle:auth-api');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/password/change/request', [PasswordController::class, 'requestChange']);
            Route::post('/password/change/verify', [PasswordController::class, 'verifyChange']);
            Route::post('/password/change', [PasswordController::class, 'change']);
            Route::post('/email/verify/send', [EmailVerificationController::class, 'send'])->middleware('throttle:auth-api');
        });
    });

    Route::get('/service-categories', [ServiceCategoryController::class, 'index']);
    Route::get('/service-categories/{slug}', [ServiceCategoryController::class, 'show']);
    Route::get('/home-adverts', [HomeAdvertController::class, 'index']);
    Route::get('/services', [ServiceCatalogController::class, 'index']);
    Route::get('/services/{public_id}', [ServiceCatalogController::class, 'show']);
    Route::get('/services/{public_id}/addons', [ServiceCatalogController::class, 'addons']);
    Route::post('/price/estimate', PriceEstimateController::class);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/devices/fcm-token', [DeviceTokenController::class, 'store']);
        Route::delete('/devices/fcm-token', [DeviceTokenController::class, 'destroy']);
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/test', [NotificationController::class, 'test']);
        Route::post('/notifications/{notificationRecord}/read', [NotificationController::class, 'markRead']);
        Route::delete('/notifications/{notificationRecord}', [NotificationController::class, 'destroy']);

        Route::prefix('provider')->group(function () {
            Route::post('/profile', [ProviderProfileController::class, 'upsert'])->middleware('role:provider');

            Route::middleware('role:provider')->group(function () {
                Route::post('/documents', [ProviderDocumentController::class, 'store']);
                Route::post('/services', [ProviderServiceController::class, 'sync']);
                Route::get('/verification', [ProviderVerificationController::class, 'show']);
                Route::post('/verification/session', [ProviderVerificationController::class, 'storeSession'])
                    ->middleware('throttle:auth-api');
                Route::post('/availability/online', [ProviderAvailabilityController::class, 'online']);
                Route::post('/availability/offline', [ProviderAvailabilityController::class, 'offline']);
                Route::post('/heartbeat', [ProviderAvailabilityController::class, 'heartbeat']);
                Route::get('/offers', [ProviderOfferController::class, 'index']);
                Route::post('/offers/{order_public_id}/skip', [ProviderOfferController::class, 'skip']);
                Route::post('/offers/{order_public_id}/accept', [ProviderOfferController::class, 'accept']);
                Route::get('/orders', [ProviderOrderController::class, 'index']);
                Route::get('/orders/active', [ProviderOrderController::class, 'active']);
                Route::get('/orders/{order_public_id}', [ProviderOrderController::class, 'show']);
                Route::post('/orders/{order_public_id}/status', [ProviderOrderController::class, 'updateStatus']);
                Route::post('/orders/{order_public_id}/cancel', [ProviderOrderController::class, 'cancel']);
                Route::post('/orders/{order_public_id}/location', [ProviderOrderController::class, 'updateLocation']);
            });
        });

        Route::middleware('role:user')->group(function () {
            Route::post('/customer/profile', [CustomerProfileController::class, 'upsert']);
            Route::get('/customer/email-preferences', [EmailPreferenceController::class, 'show']);
            Route::post('/customer/email-preferences', [EmailPreferenceController::class, 'update']);
            Route::get('/customer/verification', [UserIdentityVerificationController::class, 'show']);
            Route::post('/customer/verification', [UserIdentityVerificationController::class, 'store']);
            Route::post('/customer/verification/session', [UserIdentityVerificationSessionController::class, 'store'])
                ->middleware('throttle:auth-api');
            Route::post('/orders/pair-provider/preview', [UserOrderController::class, 'previewPairProvider']);
            Route::post('/orders', [UserOrderController::class, 'store']);
            Route::get('/orders', [UserOrderController::class, 'index']);
            Route::get('/orders/{public_id}', [UserOrderController::class, 'show']);
            Route::post('/orders/{public_id}/cancel', [UserOrderController::class, 'cancel']);
            Route::post('/orders/{public_id}/rate', [OrderRatingController::class, 'store']);
            Route::post('/disputes', [DisputeController::class, 'store']);
        });
    });
});
