<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\LoginOtpMail;
use App\Mail\NewUserNotification;
use App\Models\LoginOtp;
use App\Models\SystemSetting;
use App\Models\UserDetails;
use App\Models\UsersAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Settings Helpers
    |--------------------------------------------------------------------------
    */

    private function settingValue(string $field, $default = null)
    {
        $value = SystemSetting::where('ss_field', $field)->value('ss_value');

        return $value === null || trim((string) $value) === ''
            ? $default
            : $value;
    }

    private function integerSetting(string $field, int $default): int
    {
        $value = filter_var(
            $this->settingValue($field, $default),
            FILTER_VALIDATE_INT
        );

        return ($value !== false && $value > 0) ? $value : $default;
    }

    private function isOtpEnabled(): bool
    {
        return in_array(
            strtolower(trim((string) $this->settingValue('login_enable_otp', 'Off'))),
            ['on', 'true', '1', 'yes', 'enabled'],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | OTP Helpers
    |--------------------------------------------------------------------------
    */

    private function generateLoginOtp($user): string
    {
        LoginOtp::where('uacc_reference', $user->uacc_reference)->delete();

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        LoginOtp::create([
            'uacc_reference' => $user->uacc_reference,
            'otp_hash' => Hash::make($otp),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(5),
            'verified_at' => null,
        ]);

        return $otp;
    }

    private function sendLoginOtp($user): void
    {
        $otp = $this->generateLoginOtp($user);
        $systemName = $this->settingValue('site_name', 'System Portal');

        Mail::to($user->uacc_email)->send(
            new LoginOtpMail($otp, $systemName)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Response / Logging Helpers
    |--------------------------------------------------------------------------
    */

    private function userResponse(UsersAccount $user): array
    {
        $user->loadMissing('userDetails');

        return [
            'id' => $user->id,
            'uacc_reference' => $user->uacc_reference,
            'uacc_email' => $user->uacc_email,
            'uacc_type' => $user->uacc_type,
            'uacc_status' => $user->uacc_status,
            'uacc_email_verified_at' => $user->uacc_email_verified_at,
            'uacc_failed_login_attempts' => $user->uacc_failed_login_attempts,
            'uacc_locked_at' => $user->uacc_locked_at,
            'uacc_locked_until' => $user->uacc_locked_until,
            'uacc_last_login_at' => $user->uacc_last_login_at,
            'uacc_last_login_ip' => $user->uacc_last_login_ip,
            'uacc_password_changed_at' => $user->uacc_password_changed_at,
            'uacc_password_expires_at' => $user->uacc_password_expires_at,
            'uacc_two_factor_enabled' => $user->uacc_two_factor_enabled,
            'uacc_two_factor_confirmed_at' => $user->uacc_two_factor_confirmed_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'user_details' => $user->userDetails,
        ];
    }

    private function logLoginAttempt(
        UsersAccount $user,
        string $status,
        Request $request,
        ?string $failureReason = null
    ): void {
        $userAgent = $request->userAgent();
        $device = null;
        $browser = null;
        $platform = null;

        if ($userAgent) {
            $browser = $this->detectBrowser($userAgent);
            $platform = $this->detectPlatform($userAgent);
            $device = $this->detectDevice($userAgent);
        }

        $user->loginLogs()->create([
            'uacc_id' => $user->id,
            'uacc_reference' => $user->uacc_reference,
            'uacc_email' => $user->uacc_email,
            'uacc_type' => $user->uacc_type,
            'uacc_login_status' => $status,
            'uacc_failure_reason' => $failureReason,
            'uacc_ip_address' => $request->ip(),
            'uacc_user_agent' => $userAgent,
            'uacc_device' => $device,
            'uacc_browser' => $browser,
            'uacc_platform' => $platform,
            'uacc_session_id' => session()->getId(),
            'uacc_logged_at' => now(),
        ]);
    }

    private function detectBrowser(string $userAgent): string
    {
        if (str_contains($userAgent, 'Edg/')) {
            return 'Microsoft Edge';
        }

        if (str_contains($userAgent, 'Chrome/')) {
            return 'Google Chrome';
        }

        if (str_contains($userAgent, 'Firefox/')) {
            return 'Mozilla Firefox';
        }

        if (str_contains($userAgent, 'Safari/')) {
            return 'Safari';
        }

        if (str_contains($userAgent, 'Opera')) {
            return 'Opera';
        }

        return 'Unknown';
    }

    private function detectPlatform(string $userAgent): string
    {
        if (str_contains($userAgent, 'Windows')) {
            return 'Windows';
        }

        if (str_contains($userAgent, 'Mac OS')) {
            return 'macOS';
        }

        if (str_contains($userAgent, 'Android')) {
            return 'Android';
        }

        if (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            return 'iOS';
        }

        if (str_contains($userAgent, 'Linux')) {
            return 'Linux';
        }

        return 'Unknown';
    }

    private function detectDevice(string $userAgent): string
    {
        if (
            str_contains($userAgent, 'Mobile') ||
            str_contains($userAgent, 'Android') ||
            str_contains($userAgent, 'iPhone')
        ) {
            return 'Mobile';
        }

        if (str_contains($userAgent, 'iPad')) {
            return 'Tablet';
        }

        return 'Desktop';
    }

    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    */

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uacc_email' => ['required', 'string', 'email', 'max:255', 'unique:users_account,uacc_email'],
            'uacc_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->uacc_email));
        $temporaryPassword = $request->uacc_password;

        do {
            $reference = 'UACC' . strtoupper(Str::random(6));
        } while (UsersAccount::where('uacc_reference', $reference)->exists());

        try {
            $user = DB::transaction(function () use ($reference, $email, $temporaryPassword) {
                $user = UsersAccount::create([
                    'uacc_reference' => $reference,
                    'uacc_email' => $email,
                    'uacc_email_verified_at' => null,
                    'uacc_password' => Hash::make($temporaryPassword),
                    'uacc_type' => 'User',
                    'uacc_status' => 'Active',
                    'uacc_failed_login_attempts' => 0,
                    'uacc_password_changed_at' => null,
                ]);

                UserDetails::create([
                    'ud_uniqid' => $user->uacc_reference,
                    'ud_email' => $user->uacc_email,
                    'ud_created_at' => now(),
                ]);

                return $user;
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create the account.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }

        $emailSent = false;

        try {
            Mail::to($user->uacc_email)->send(
                new NewUserNotification($user, $temporaryPassword)
            );

            $emailSent = true;
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'success' => true,
            'email_verification_required' => true,
            'email_verified' => false,
            'email_sent' => $emailSent,
            'message' => $emailSent
                ? 'Registration successful. Please check your email and verify your account.'
                : 'Registration successful, but the verification email could not be sent. Please use the resend verification option.',
            'user' => [
                'id' => $user->id,
                'uacc_reference' => $user->uacc_reference,
                'uacc_email' => $user->uacc_email,
                'uacc_type' => $user->uacc_type,
                'uacc_status' => $user->uacc_status,
                'uacc_email_verified_at' => $user->uacc_email_verified_at,
            ],
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uacc_email' => ['required', 'email'],
            'uacc_password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->uacc_email));
        $password = $request->uacc_password;
        $user = UsersAccount::where('uacc_email', $email)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if ($user->uacc_status === 'Deleted') {
            return response()->json([
                'success' => false,
                'message' => 'Account not found.',
            ], 404);
        }

        if ($user->uacc_status === 'Inactive') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive.',
            ], 403);
        }

        if ($user->uacc_status === 'Suspended') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been suspended.',
            ], 403);
        }

        if (
            $user->uacc_status === 'Locked' &&
            $user->uacc_locked_until &&
            now()->lessThan($user->uacc_locked_until)
        ) {
            $this->logLoginAttempt(
                $user,
                'Locked',
                $request,
                'Account temporarily locked.'
            );

            return response()->json([
                'success' => false,
                'message' => 'Account temporarily locked. Please try again later.',
                'locked_until' => $user->uacc_locked_until,
            ], 423);
        }

        if (
            $user->uacc_status === 'Locked' &&
            $user->uacc_locked_until &&
            now()->greaterThanOrEqualTo($user->uacc_locked_until)
        ) {
            $user->update([
                'uacc_status' => 'Active',
                'uacc_failed_login_attempts' => 0,
                'uacc_locked_at' => null,
                'uacc_locked_until' => null,
            ]);
        }

        if (! Hash::check($password, $user->uacc_password)) {
            $failedAttempts = ((int) $user->uacc_failed_login_attempts) + 1;
            $maxAttempts = $this->integerSetting('login_max_attempts', 3);

            if ($failedAttempts >= $maxAttempts) {
                $lockoutMinutes = $this->integerSetting('login_lockout_minutes', 15);
                $lockedUntil = now()->addMinutes($lockoutMinutes);

                $user->update([
                    'uacc_failed_login_attempts' => $failedAttempts,
                    'uacc_status' => 'Locked',
                    'uacc_locked_at' => now(),
                    'uacc_locked_until' => $lockedUntil,
                ]);

                $this->logLoginAttempt(
                    $user,
                    'Locked',
                    $request,
                    'Too many failed login attempts.'
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Too many failed login attempts. Your account has been temporarily locked.',
                    'locked_until' => $lockedUntil,
                    'remaining_attempts' => 0,
                ], 423);
            }

            $user->update([
                'uacc_failed_login_attempts' => $failedAttempts,
            ]);

            $this->logLoginAttempt(
                $user,
                'Failed',
                $request,
                'Invalid password.'
            );

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
                'remaining_attempts' => $maxAttempts - $failedAttempts,
            ], 401);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'email_verification_required' => true,
                'email_verified' => false,
                'message' => 'Please verify your email address before logging in.',
                'uacc_email' => $user->uacc_email,
            ], 403);
        }

        if ($this->isOtpEnabled()) {
            try {
                $this->sendLoginOtp($user);
            } catch (\Throwable $e) {
                report($e);

                return response()->json([
                    'success' => false,
                    'message' => 'Unable to send the login verification code. Please try again later.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP verification required.',
                'otp_required' => true,
                'email' => $user->uacc_email,
            ], 200);
        }

        return $this->completeLogin($user, $request);
    }

    private function completeLogin(UsersAccount $user, Request $request)
    {
        $user->update([
            'uacc_failed_login_attempts' => 0,
            'uacc_locked_at' => null,
            'uacc_locked_until' => null,
            'uacc_status' => 'Active',
            'uacc_last_login_at' => now(),
            'uacc_last_login_ip' => $request->ip(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $this->logLoginAttempt($user, 'Success', $request);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->userResponse($user),
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | Login OTP
    |--------------------------------------------------------------------------
    */

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uacc_email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
            'remember_me' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->uacc_email));
        $otp = trim((string) $request->otp);
        $user = UsersAccount::where('uacc_email', $email)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification request.',
            ], 422);
        }

        if ($user->uacc_status !== 'Active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is currently inactive. Please contact your system administrator.',
            ], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email address before continuing.',
                'email_verification_required' => true,
            ], 403);
        }

        $otpRecord = LoginOtp::where('uacc_reference', $user->uacc_reference)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'No active verification code was found. Please request a new code.',
                'otp_missing' => true,
            ], 422);
        }

        $expiresAt = $otpRecord->expires_at;

        if (! $expiresAt || now()->greaterThan($expiresAt)) {
            $otpRecord->delete();

            return response()->json([
                'success' => false,
                'message' => 'Your verification code has expired. Please request a new code.',
                'otp_expired' => true,
            ], 422);
        }

        $maxOtpAttempts = 5;
        $attemptsUsed = (int) $otpRecord->attempts;

        if ($attemptsUsed >= $maxOtpAttempts) {
            $otpRecord->delete();

            return response()->json([
                'success' => false,
                'message' => 'Too many invalid OTP attempts. Please request a new code.',
                'otp_locked' => true,
            ], 429);
        }

        if (! Hash::check($otp, $otpRecord->otp_hash)) {
            $otpRecord->increment('attempts');

            $attemptsUsed = (int) $otpRecord->fresh()->attempts;
            $remainingAttempts = max(0, $maxOtpAttempts - $attemptsUsed);

            if ($remainingAttempts <= 0) {
                $otpRecord->delete();

                return response()->json([
                    'success' => false,
                    'message' => 'Too many invalid OTP attempts. Please request a new code.',
                    'otp_locked' => true,
                    'remaining_attempts' => 0,
                ], 429);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code.',
                'remaining_attempts' => $remainingAttempts,
            ], 422);
        }

        $otpRecord->update([
            'verified_at' => now(),
        ]);

        // OTP verification is the final authentication step.
        $user->update([
            'uacc_failed_login_attempts' => 0,
            'uacc_locked_at' => null,
            'uacc_locked_until' => null,
            'uacc_status' => 'Active',
            'uacc_last_login_at' => now(),
            'uacc_last_login_ip' => $request->ip(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $this->logLoginAttempt($user, 'Success', $request);

        $user->load('userDetails');

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'otp_verified' => true,
            'user' => $this->userResponse($user),
        ], 200);
    }

    public function resendOtp(Request $request)
    {
        $validated = $request->validate([
            'uacc_email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($validated['uacc_email']));
        $user = UsersAccount::where('uacc_email', $email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'If the account exists, a new verification code has been sent.',
                'otp_sent' => true,
            ], 200);
        }

        if ($user->uacc_status !== 'Active') {
            return response()->json([
                'message' => 'Your account is currently inactive.',
            ], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Please verify your email address first.',
                'email_verification_required' => true,
            ], 403);
        }

        try {
            $this->sendLoginOtp($user);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Unable to send a new verification code. Please try again later.',
            ], 500);
        }

        return response()->json([
            'message' => 'A new verification code has been sent to your email address.',
            'otp_sent' => true,
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | Authenticated User
    |--------------------------------------------------------------------------
    */

    public function me(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'email_verification_required' => true,
                'email_verified' => false,
                'message' => 'Email address has not been verified.',
                'uacc_email' => $user->uacc_email,
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Authenticated user.',
            'user' => $this->userResponse($user),
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $this->logLoginAttempt($user, 'Logout', $request);

            if ($user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out.',
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | Account Changes
    |--------------------------------------------------------------------------
    */

    public function changeEmail(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'current_email' => ['required', 'string', 'email', 'max:255'],
                'new_email' => ['required', 'string', 'email', 'max:255', 'different:current_email'],
                'new_email_confirmation' => ['required', 'same:new_email'],
            ],
            [
                'current_email.required' => 'Your current email address is required.',
                'current_email.email' => 'Please enter a valid current email address.',
                'new_email.required' => 'Your new email address is required.',
                'new_email.email' => 'Please enter a valid new email address.',
                'new_email.different' => 'Your new email address must be different from your current email address.',
                'new_email_confirmation.required' => 'Please confirm your new email address.',
                'new_email_confirmation.same' => 'The email confirmation does not match the new email address.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $currentEmail = strtolower(trim($request->current_email));
        $newEmail = strtolower(trim($request->new_email));
        $accountEmail = strtolower(trim($user->uacc_email));

        if ($currentEmail !== $accountEmail) {
            return response()->json([
                'success' => false,
                'message' => 'The current email address is incorrect.',
                'errors' => [
                    'current_email' => [
                        'The current email address does not match your account.',
                    ],
                ],
            ], 422);
        }

        $existingUser = UsersAccount::whereRaw('LOWER(uacc_email) = ?', [$newEmail])
            ->where('id', '!=', $user->id)
            ->first();

        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'The new email address is already being used by another account.',
                'errors' => [
                    'new_email' => [
                        'The email address has already been taken.',
                    ],
                ],
            ], 422);
        }

        $userDetails = UserDetails::where('ud_uniqid', $user->uacc_reference)->first();

        if (! $userDetails) {
            $userDetails = UserDetails::where('ud_email', $user->uacc_email)->first();
        }

        try {
            DB::transaction(function () use ($user, $userDetails, $newEmail) {
                $user->uacc_email = $newEmail;
                $user->uacc_email_verified_at = null;
                $user->save();

                if ($userDetails) {
                    $userDetails->ud_email = $newEmail;
                    $userDetails->ud_uniqid = $user->uacc_reference;
                    $userDetails->save();
                } else {
                    UserDetails::create([
                        'ud_uniqid' => $user->uacc_reference,
                        'ud_email' => $newEmail,
                        'ud_created_at' => now(),
                    ]);
                }
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update your email address.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }

        $user->refresh();
        $user->load('userDetails');

        $verificationEmailSent = false;

        try {
            $user->sendEmailVerificationNotification();
            $verificationEmailSent = true;
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'success' => true,
            'email_verification_required' => true,
            'email_verified' => false,
            'verification_email_sent' => $verificationEmailSent,
            'message' => $verificationEmailSent
                ? 'Email address updated successfully. Please verify your new email address.'
                : 'Email address updated successfully, but the verification email could not be sent.',
            'user' => $this->userResponse($user),
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'current_password' => ['required', 'string'],
                'new_password' => ['required', 'string', 'min:8', 'different:current_password', 'confirmed'],
            ],
            [
                'current_password.required' => 'Your current password is required.',
                'new_password.required' => 'Your new password is required.',
                'new_password.min' => 'Your new password must be at least 8 characters.',
                'new_password.different' => 'Your new password must be different from your current password.',
                'new_password.confirmed' => 'The new password confirmation does not match.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (! Hash::check($request->current_password, $user->uacc_password)) {
            return response()->json([
                'success' => false,
                'message' => 'The current password is incorrect.',
                'errors' => [
                    'current_password' => [
                        'The current password is incorrect.',
                    ],
                ],
            ], 422);
        }

        try {
            $user->uacc_password = Hash::make($request->new_password);
            $user->uacc_password_changed_at = now();

            if (! empty($user->uacc_password_expires_at)) {
                $user->uacc_password_expires_at = now()->addDays(90);
            }

            $user->uacc_failed_login_attempts = 0;
            $user->uacc_locked_at = null;
            $user->uacc_locked_until = null;

            if ($user->uacc_status === 'Locked') {
                $user->uacc_status = 'Active';
            }

            $user->save();
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to change your password.',
            ], 500);
        }

        $user->refresh();
        $user->loadMissing('userDetails');

        return response()->json([
            'success' => true,
            'message' => 'Your password has been changed successfully.',
            'user' => $this->userResponse($user),
        ], 200);
    }
}