<?php

use App\Http\Controllers\Admin\CommissionRuleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DisputeController;
use App\Http\Controllers\Admin\HomeAdvertController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\ServiceAddOnController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ServicePricingRuleController;
use App\Http\Controllers\Admin\TransportZoneController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserIdentityVerificationController;
use App\Http\Controllers\Admin\WalletController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\EmailPreferenceController;
use App\Http\Controllers\Web\VerificationSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', function () {
    return redirect('/');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:auth-api'])
    ->name('verification.verify');

Route::get('/verify/identity/{session}', [VerificationSessionController::class, 'show'])
    ->name('verification.sessions.show');
Route::post('/verify/identity/{session}', [VerificationSessionController::class, 'store'])
    ->name('verification.sessions.submit');

Route::get('/email/preferences/{user}', [EmailPreferenceController::class, 'show'])
    ->name('email-preferences.show');
Route::post('/email/preferences/{user}', [EmailPreferenceController::class, 'update'])
    ->name('email-preferences.update');

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:superadmin|admin'])
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::resource('service-categories', ServiceCategoryController::class);
        Route::resource('transport-zones', TransportZoneController::class);
        Route::resource('home-adverts', HomeAdvertController::class);
        Route::resource('services', ServiceController::class);
        Route::resource('addons', ServiceAddOnController::class)->parameters(['addons' => 'serviceAddOn']);
        Route::resource('pricing-rules', ServicePricingRuleController::class)->parameters(['pricing-rules' => 'servicePricingRule']);

        Route::get('/providers', [ProviderController::class, 'index'])->name('providers.index');
        Route::get('/providers/{provider}', [ProviderController::class, 'show'])->name('providers.show');
        Route::post('/providers/{provider}/verification', [ProviderController::class, 'updateVerification'])->name('providers.verification.update');
        Route::get('/providers/{provider}/verification-media/{collection}', [ProviderController::class, 'verificationMedia'])->name('providers.verification.media');
        Route::delete('/providers/{provider}/verification-media/{collection}', [ProviderController::class, 'destroyVerificationMedia'])->name('providers.verification.media.destroy');
        Route::post('/providers/{provider}/services', [ProviderController::class, 'updateServices'])->name('providers.services.update');
        Route::get('/provider-documents/{document}/media', [ProviderController::class, 'documentMedia'])->name('provider-documents.media');
        Route::resource('users', UserController::class)->only(['index', 'edit', 'update', 'destroy']);
        Route::get('/user-identity-verifications', [UserIdentityVerificationController::class, 'index'])->name('user-identity-verifications.index');
        Route::get('/user-identity-verifications/{verification}', [UserIdentityVerificationController::class, 'show'])->name('user-identity-verifications.show');
        Route::post('/user-identity-verifications/{verification}/review', [UserIdentityVerificationController::class, 'review'])->name('user-identity-verifications.review');
        Route::get('/user-identity-verifications/{verification}/media/{collection}', [UserIdentityVerificationController::class, 'media'])->name('user-identity-verifications.media');
        Route::delete('/user-identity-verifications/{verification}/media/{collection}', [UserIdentityVerificationController::class, 'destroyMedia'])->name('user-identity-verifications.media.destroy');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

        Route::get('/wallets', [WalletController::class, 'index'])->name('wallets.index');
        Route::get('/wallets/{wallet}', [WalletController::class, 'show'])->name('wallets.show');
        Route::post('/wallets/{wallet}/adjust', [WalletController::class, 'adjust'])->name('wallets.adjust');

        Route::resource('commission-rules', CommissionRuleController::class)->parameters(['commission-rules' => 'commissionRule']);

        Route::get('/disputes', [DisputeController::class, 'index'])->name('disputes.index');
        Route::post('/disputes/{dispute}/resolve', [DisputeController::class, 'resolve'])->name('disputes.resolve');
    });
