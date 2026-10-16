<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UsersAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Token expiration in minutes.
     */
    private const TOKEN_EXPIRE_MINUTES = 60;

    /**
     * Send password reset link.
     */
    public function sendResetLink(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uacc_email' => [
                'required',
                'email',
                'max:100',
            ],
        ]);

        $email = strtolower(trim($validated['uacc_email']));

        /*
        |--------------------------------------------------------------------------
        | Find account
        |--------------------------------------------------------------------------
        */

        $user = UsersAccount::whereRaw(
            'LOWER(uacc_email) = ?',
            [$email]
        )->first();

        /*
        |--------------------------------------------------------------------------
        | Do not reveal whether email exists
        |--------------------------------------------------------------------------
        */

        if (!$user) {
            return response()->json([
                'message' => 'The email address is not registered.',
                'errors' => [
                    'uacc_email' => ['The email address is not registered.'],
                ],
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Optional account-status protection
        |--------------------------------------------------------------------------
        */

        if (
            isset($user->uacc_status) &&
            !in_array(
                $user->uacc_status,
                ['Active'],
                true
            )
        ) {
            return response()->json([
                'message' => 'Password reset is not available for this account. Please contact your system administrator.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Generate secure token
        |--------------------------------------------------------------------------
        */

        $plainToken = Str::random(64);

        $hashedToken = hash(
            'sha256',
            $plainToken
        );

        /*
        |--------------------------------------------------------------------------
        | Remove previous token
        |--------------------------------------------------------------------------
        */

        DB::table('password_reset_tokens')
            ->where('uacc_email', $email)
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | Store token
        |--------------------------------------------------------------------------
        */

        DB::table('password_reset_tokens')->insert([
            'uacc_email' => $email,
            'uacc_token' => $hashedToken,
            'uacc_created_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Reset URL
        |--------------------------------------------------------------------------
        */

        $frontendUrl = rtrim(
            config(
                'app.frontend_url',
                env('FRONTEND_URL', 'http://localhost:5173')
            ),
            '/'
        );

        $resetUrl =
            $frontendUrl .
            '/reset-password?token=' .
            urlencode($plainToken) .
            '&email=' .
            urlencode($email);

        /*
        |--------------------------------------------------------------------------
        | Send email
        |--------------------------------------------------------------------------
        */

        try {
            Mail::send(
                'emails.password-reset',
                [
                    'user' => $user,
                    'resetUrl' => $resetUrl,
                    'expiresIn' => self::TOKEN_EXPIRE_MINUTES,
                ],
                function ($message) use ($email) {
                    $message
                        ->to($email)
                        ->subject('Password Reset Request');
                }
            );
        } catch (\Throwable $e) {

            report($e);

            /*
            |--------------------------------------------------------------------------
            | Delete token if email failed
            |--------------------------------------------------------------------------
            */

            DB::table('password_reset_tokens')
                ->where('uacc_email', $email)
                ->delete();

            return response()->json([
                'message' => 'Unable to send the password reset email. Please try again later.',
            ], 500);
        }

        return response()->json([
            'message' => 'If an account exists for this email address, a password reset link has been sent.',
        ]);
    }

    /**
     * Reset password.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:100',
            ],
            'token' => [
                'required',
                'string',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed',
            ],
        ]);

        $email = strtolower(
            trim($validated['email'])
        );

        /*
        |--------------------------------------------------------------------------
        | Find reset token
        |--------------------------------------------------------------------------
        */

        $resetRecord = DB::table('password_reset_tokens')
            ->where('uacc_email', $email)
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'message' => 'This password reset link is invalid or has expired.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Check expiration
        |--------------------------------------------------------------------------
        */

        $createdAt = Carbon::parse(
            $resetRecord->uacc_created_at
        );

        if (
            $createdAt->addMinutes(
                self::TOKEN_EXPIRE_MINUTES
            )->isPast()
        ) {
            DB::table('password_reset_tokens')
                ->where('uacc_email', $email)
                ->delete();

            return response()->json([
                'message' => 'This password reset link has expired. Please request a new one.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Verify token
        |--------------------------------------------------------------------------
        */

        $hashedToken = hash(
            'sha256',
            $validated['token']
        );

        if (
            !hash_equals(
                $resetRecord->uacc_token,
                $hashedToken
            )
        ) {
            return response()->json([
                'message' => 'This password reset link is invalid or has expired.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Find user
        |--------------------------------------------------------------------------
        */

        $user = UsersAccount::whereRaw(
            'LOWER(uacc_email) = ?',
            [$email]
        )->first();

        if (!$user) {
            DB::table('password_reset_tokens')
                ->where('uacc_email', $email)
                ->delete();

            return response()->json([
                'message' => 'Unable to reset the password for this account.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Update password
        |--------------------------------------------------------------------------
        */

        $user->uacc_password = Hash::make(
            $validated['password']
        );

        /*
        |--------------------------------------------------------------------------
        | Optional password-change timestamp
        |--------------------------------------------------------------------------
        */

        if (
            $user->getConnection()
                ->getSchemaBuilder()
                ->hasColumn(
                    $user->getTable(),
                    'uacc_password_changed_at'
                )
        ) {
            $user->uacc_password_changed_at = now();
        }

        /*
        |--------------------------------------------------------------------------
        | Optional password expiration reset
        |--------------------------------------------------------------------------
        */

        if (
            $user->getConnection()
                ->getSchemaBuilder()
                ->hasColumn(
                    $user->getTable(),
                    'uacc_password_expires_at'
                )
        ) {
            $user->uacc_password_expires_at = null;
        }

        $user->save();

        /*
        |--------------------------------------------------------------------------
        | Delete used token
        |--------------------------------------------------------------------------
        */

        DB::table('password_reset_tokens')
            ->where('uacc_email', $email)
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | Revoke existing API tokens
        |--------------------------------------------------------------------------
        */

        if (
            method_exists(
                $user,
                'tokens'
            )
        ) {
            try {
                $user->tokens()->delete();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            'message' => 'Your password has been reset successfully. You can now sign in with your new password.',
        ]);
    }
}