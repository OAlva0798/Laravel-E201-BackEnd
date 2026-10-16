<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UsersAccount;
use Illuminate\Http\Request;
use App\Mail\NewUserNotification;
use Illuminate\Support\Facades\Mail;

class EmailVerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Verify Email
    |--------------------------------------------------------------------------
    */

    public function verify(Request $request, string $id, string $hash)
    {
        $frontendUrl = rtrim(
            env(
                'FRONTEND_URL',
                'http://localhost:5173'
            ),
            '/'
        );

        /*
        |--------------------------------------------------------------------------
        | Validate Signed URL
        |--------------------------------------------------------------------------
        */

        if (!$request->hasValidSignature()) {

            return redirect()->away(
                $frontendUrl .
                '/verify-email?status=error&message=' .
                urlencode(
                    'The verification link is invalid or has expired.'
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Find Account
        |--------------------------------------------------------------------------
        */

        $user = UsersAccount::find($id);

        if (!$user) {

            return redirect()->away(
                $frontendUrl .
                '/verify-email?status=error&message=' .
                urlencode(
                    'The account associated with this verification link could not be found.'
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Deleted Account
        |--------------------------------------------------------------------------
        */

        if ($user->uacc_status === 'Deleted') {

            return redirect()->away(
                $frontendUrl .
                '/verify-email?status=error&message=' .
                urlencode(
                    'This account has been deleted.'
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Email Hash
        |--------------------------------------------------------------------------
        */

        $expectedHash = sha1(
            $user->getEmailForVerification()
        );

        if (!hash_equals(
            $expectedHash,
            (string) $hash
        )) {

            return redirect()->away(
                $frontendUrl .
                '/verify-email?status=error&message=' .
                urlencode(
                    'The email verification link is invalid.'
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Already Verified
        |--------------------------------------------------------------------------
        */

        if ($user->hasVerifiedEmail()) {

            return redirect()->away(
                $frontendUrl .
                '/verify-email?status=already_verified'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Mark Email Verified
        |--------------------------------------------------------------------------
        */

        $user->markEmailAsVerified();

        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        return redirect()->away(
            $frontendUrl .
            '/verify-email?status=success'
        );
    }
/**
 * Resend Verification
 */
public function resend(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | Validate Request
    |--------------------------------------------------------------------------
    */

    $request->validate([
        'uacc_email' => [
            'required',
            'email',
        ],
    ]);

    /*
    |--------------------------------------------------------------------------
    | Normalize Email
    |--------------------------------------------------------------------------
    */

    $email = strtolower(
        trim($request->uacc_email)
    );

    /*
    |--------------------------------------------------------------------------
    | Find Account
    |--------------------------------------------------------------------------
    */

    $user = UsersAccount::where(
        'uacc_email',
        $email
    )->first();

    /*
    |--------------------------------------------------------------------------
    | Do Not Reveal Account Existence
    |--------------------------------------------------------------------------
    */

    if (
        !$user ||
        $user->uacc_status === 'Deleted'
    ) {
        return response()->json([
            'success' =>
                true,

            'message' =>
                'If the account exists, a verification email has been sent.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Already Verified
    |--------------------------------------------------------------------------
    */

    if ($user->hasVerifiedEmail()) {
        return response()->json([
            'success' =>
                true,

            'email_verified' =>
                true,

            'email_verification_required' =>
                false,

            'message' =>
                'This email address has already been verified.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Send Verification Email
    |--------------------------------------------------------------------------
    */

    try {

        Mail::to(
            $user->uacc_email
        )->send(
            new NewUserNotification(
                $user
            )
        );

        return response()->json([
            'success' =>
                true,

            'email_verification_required' =>
                true,

            'email_verified' =>
                false,

            'email_sent' =>
                true,

            'message' =>
                'A new verification email has been sent.',
        ]);

    } catch (\Throwable $e) {

        report($e);

        return response()->json([
            'success' =>
                false,

            'email_sent' =>
                false,

            'message' =>
                'The verification email could not be sent. Please try again later.',
        ], 500);
    }
}
}