<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CenterController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PositionController;
use App\Http\Controllers\Api\SystemSettingController;
use App\Http\Controllers\Api\UserDetailsController;
use App\Http\Controllers\Api\EmailDispatchController;

/*
|--------------------------------------------------------------------------
| Public Authentication
|--------------------------------------------------------------------------
|
| These routes do not require auth:sanctum.
|
| Login flow:
|
| 1. POST /login
| 2. If OTP is enabled: otp_required = true
| 3. Frontend redirects to /verify-otp?email=...&remember_me=1
| 4. POST /verify-otp
| 5. Backend validates LoginOtp using uacc_reference
| 6. Backend creates Sanctum token
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Login OTP
|--------------------------------------------------------------------------
|
| OTP verification must remain public because the user does not
| have an access token before OTP verification succeeds.
|
*/

Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])
    ->middleware('throttle:10,1');

Route::post('/resend-otp', [AuthController::class, 'resendOtp'])
    ->middleware('throttle:5,1');

/*
|--------------------------------------------------------------------------
| Password Reset
|--------------------------------------------------------------------------
*/

Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
    ->middleware('throttle:5,1');

Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

/*
|--------------------------------------------------------------------------
| Email Verification
|--------------------------------------------------------------------------
*/

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('throttle:6,1')
    ->name('verification.verify');

Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
    ->middleware('throttle:6,1');

Route::post('/email-dispatch', [EmailDispatchController::class, 'send'])
    ->middleware('throttle:6,1');
/*
|--------------------------------------------------------------------------
| Lookup Routes
|--------------------------------------------------------------------------
*/

Route::get('/centers/active-centers', [CenterController::class, 'getCenters']);
Route::get('/departments/active-departments', [DepartmentController::class, 'getDepartments']);
Route::get('/positions/active-positions', [PositionController::class, 'getPositions']);
Route::get('/user-details/active-user-details', [UserDetailsController::class, 'getUserDetails']);

/*
|--------------------------------------------------------------------------
| System Settings
|--------------------------------------------------------------------------
|
| Note: /system-settings/SystemInfo must stay above /system-settings/{id}.
|
*/

Route::get('/system-settings', [SystemSettingController::class, 'index']);
Route::get('/system-settings/SystemInfo', [SystemSettingController::class, 'getSystemInfo']);
Route::post('/system-settings', [SystemSettingController::class, 'store']);
Route::get('/system-settings/{id}', [SystemSettingController::class, 'show']);
Route::put('/system-settings/{id}', [SystemSettingController::class, 'update']);
Route::patch('/system-settings/{id}', [SystemSettingController::class, 'update']);
Route::delete('/system-settings/{id}', [SystemSettingController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Maintenance CRUD Resources
|--------------------------------------------------------------------------
*/

Route::apiResource('centers', CenterController::class);
Route::apiResource('departments', DepartmentController::class);
Route::apiResource('positions', PositionController::class);
Route::apiResource('user-details', UserDetailsController::class);

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
|
| These routes require a valid Laravel Sanctum access token.
|
*/

Route::middleware('auth:sanctum')->group(function () {

    // Current authenticated user
    Route::get('/me', [AuthController::class, 'me']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Change email
    Route::post('/change-email', [AuthController::class, 'changeEmail']);

    // Change password
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    /*
    |----------------------------------------------------------------------
    | Authenticated User Details
    |----------------------------------------------------------------------
    |
    | Compatibility endpoint for existing frontend code.
    |
    */

    Route::get('/user', function (Request $request) {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $user->loadMissing('userDetails');

        return response()->json([
            'success' => true,
            'message' => 'Authenticated user.',
            'user' => [
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
            ],
        ]);
    });
});