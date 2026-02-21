<?php

// app/Http/Controllers/Api/V1/Auth/PasswordController.php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    public function forgot(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email'],
        ]);

        $status = Password::sendResetLink(['email' => strtolower($data['email'])]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['message' => 'Password reset link sent.']);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token' => ['required','string'],
            'email' => ['required','email'],
            'password' => ['required','string','min:8','confirmed'],
        ]);

        $status = Password::reset(
            [
                'email' => strtolower($data['email']),
                'password' => $data['password'],
                'password_confirmation' => $request->input('password_confirmation'),
                'token' => $data['token'],
            ],
            function ($user) use ($data) {
                $user->forceFill([
                    'password' => bcrypt($data['password']),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['message' => 'Password reset successful.']);
    }
}
