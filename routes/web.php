<?php

use Illuminate\Support\Facades\Route;
// routes/web.php or routes/api.php (web is better)
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return response()->json(['message' => 'Email verified.']);
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');
