<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\AuthController;
use App\Http\Requests\Auth\PasswordEmailRequest;
use App\Http\Requests\Auth\PasswordResetRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * @group Auth
 *
 * Authentication & account endpoints (Sanctum Bearer token).
 */
class AuthApiController extends AuthController
{
    /**
     * Send a password reset link.
     *
     * @bodyParam email string required The user's email. Example: admin@laravel-base.local
     *
     * @response 200 {"message":"We have emailed your password reset link."}
     * @response 422 {"message":"The given data was invalid.","errors":{"email":["We can't find a user with that email address."]}}
     */
    public function forgotPassword(PasswordEmailRequest $request): JsonResponse
    {
        $status = Password::broker('users')->sendResetLink($request->validated());

        if ($status === Password::RESET_LINK_SENT) {
            activity()->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'email' => $request->email,
            ])->log('password_reset_request');

            return response()->json(['message' => __($status)]);
        }

        throw ValidationException::withMessages(['email' => __($status)]);
    }

    /**
     * Reset password using a valid token.
     *
     * @bodyParam token string required The reset token from the email.
     * @bodyParam email string required The user's email.
     * @bodyParam password string required Min 8, confirmed.
     * @bodyParam password_confirmation string required
     *
     * @response 200 {"message":"Your password has been reset."}
     * @response 422 {"message":"The given data was invalid.","errors":{"email":["This password reset token is invalid."]}}
     */
    public function resetPassword(PasswordResetRequest $request): JsonResponse
    {
        $status = Password::broker('users')->reset(
            $request->validated(),
            fn ($user, $password) => $user->forceFill(['password' => bcrypt($password)])->save()
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)]);
        }

        throw ValidationException::withMessages(['email' => __($status)]);
    }

    /**
     * Verify email with the signed verification URL token.
     *
     * @queryParam id int required User id.
     * @queryParam hash string required Verification hash.
     * @queryParam signature string required Signed URL signature.
     * @queryParam expires int optional Expiry timestamp.
     *
     * @response 200 {"message":"Email verified."}
     * @response 403 {"message":"Invalid or expired verification link."}
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $user = \App\Models\User::findOrFail($request->query('id'));

        if (! hash_equals((string) $request->query('hash'), sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid or expired verification link.'], 403);
        }
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.']);
        }

        $user->markEmailAsVerified();

        return response()->json(['message' => 'Email verified.']);
    }

    /**
     * Resend the email verification link.
     *
     * @authenticated
     *
     * @response 200 {"message":"Verification link sent."}
     * @response 400 {"message":"Email already verified."}
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }
        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent.']);
    }
}
