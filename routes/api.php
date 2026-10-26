<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\CenterController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PositionController;
use App\Http\Controllers\Api\PositionLevelController;
use App\Http\Controllers\Api\SystemSettingController;
use App\Http\Controllers\Api\UserDetailsController;
use App\Http\Controllers\Api\EmailDispatchController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EmployeeDocumentController;
use App\Http\Controllers\Api\EmployeeMovementHistoryController;


Route::prefix('')->group(function () {
    /*| Public Authentication Routes */
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    /*| Login OTP Routes */
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])
        ->middleware('throttle:10,1');
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])
        ->middleware('throttle:5,1');

    /*| Password Reset Routes */
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
        ->middleware('throttle:5,1');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
});

/*
|--------------------------------------------------------------------------
| Email Verification and Dispatch Routes
|--------------------------------------------------------------------------
*/

Route::prefix('email')->group(function () {
    Route::get('/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('throttle:6,1')
        ->name('verification.verify');

    Route::post('/resend', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1');

    Route::post('-dispatch', [EmailDispatchController::class, 'send'])
        ->middleware('throttle:6,1');
});

/*
|--------------------------------------------------------------------------
| Company Routes
|--------------------------------------------------------------------------
*/

Route::prefix('company')->group(function () {
    Route::get('/active-company', [CompanyController::class, 'getCompanies']);

    Route::get('/', [CompanyController::class, 'index']);
    Route::post('/', [CompanyController::class, 'store']);
    Route::get('/{company}', [CompanyController::class, 'show']);
    Route::put('/{company}', [CompanyController::class, 'update']);
    Route::patch('/{company}', [CompanyController::class, 'update']);
    Route::delete('/{company}', [CompanyController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Center Routes
|--------------------------------------------------------------------------
*/

Route::prefix('centers')->group(function () {
    Route::get('/active-centers', [CenterController::class, 'getCenters']);

    Route::get('/', [CenterController::class, 'index']);
    Route::post('/', [CenterController::class, 'store']);
    Route::get('/{center}', [CenterController::class, 'show']);
    Route::put('/{center}', [CenterController::class, 'update']);
    Route::patch('/{center}', [CenterController::class, 'update']);
    Route::delete('/{center}', [CenterController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Department Routes
|--------------------------------------------------------------------------
*/

Route::prefix('departments')->group(function () {
    Route::get('/active-departments', [DepartmentController::class, 'getDepartments']);

    Route::get('/', [DepartmentController::class, 'index']);
    Route::post('/', [DepartmentController::class, 'store']);
    Route::get('/{department}', [DepartmentController::class, 'show']);
    Route::put('/{department}', [DepartmentController::class, 'update']);
    Route::patch('/{department}', [DepartmentController::class, 'update']);
    Route::delete('/{department}', [DepartmentController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Position Routes
|--------------------------------------------------------------------------
*/

Route::prefix('positions')->group(function () {
    Route::get('/active-positions', [PositionController::class, 'getPositions']);

    Route::get('/', [PositionController::class, 'index']);
    Route::post('/', [PositionController::class, 'store']);
    Route::get('/{position}', [PositionController::class, 'show']);
    Route::put('/{position}', [PositionController::class, 'update']);
    Route::patch('/{position}', [PositionController::class, 'update']);
    Route::delete('/{position}', [PositionController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Position Level Routes
|--------------------------------------------------------------------------
*/

Route::prefix('positionLevel')->group(function () {
    Route::get('/active-position_level', [
        PositionLevelController::class,
        'getActivePositionLevels',
    ]);

    Route::get('/', [PositionLevelController::class, 'index']);
    Route::post('/', [PositionLevelController::class, 'store']);
    Route::get('/{positionLevel}', [PositionLevelController::class, 'show']);
    Route::put('/{positionLevel}', [PositionLevelController::class, 'update']);
    Route::patch('/{positionLevel}', [PositionLevelController::class, 'update']);
    Route::delete('/{positionLevel}', [PositionLevelController::class, 'destroy']);
});

Route::prefix('employees/{idNumber}/documents')->group(function () {
    Route::get('/', [EmployeeDocumentController::class, 'index']);
    Route::post('/', [EmployeeDocumentController::class, 'store']);

    Route::get('/{documentId}', [EmployeeDocumentController::class, 'show']);
    Route::get('/{documentId}/view', [EmployeeDocumentController::class, 'view']);
    Route::get('/{documentId}/download', [EmployeeDocumentController::class, 'download']);

    Route::post('/{documentId}', [EmployeeDocumentController::class, 'update']);
    Route::delete('/{documentId}', [EmployeeDocumentController::class, 'destroy']);
});

Route::prefix('employee-movements')
    ->controller(EmployeeMovementHistoryController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');

        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::patch('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });

/*
|--------------------------------------------------------------------------
| User Details Routes
|--------------------------------------------------------------------------
*/

Route::prefix('user-details')->group(function () {
    Route::get('/active-user-details', [UserDetailsController::class,'getUserDetails',]);
    Route::patch('/{userDetail}/status', [UserDetailsController::class, 'updateStatus']);

    Route::get('/', [UserDetailsController::class, 'index']);
    Route::post('/', [UserDetailsController::class, 'store']);

    Route::get('/{userDetail}', [UserDetailsController::class, 'show']);
    Route::put('/{userDetail}', [UserDetailsController::class, 'update']);
    Route::patch('/{userDetail}', [UserDetailsController::class, 'update']);
    Route::delete('/{userDetail}', [UserDetailsController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Employee Routes
|--------------------------------------------------------------------------
*/

Route::prefix('employees')->group(function () {
    Route::get('/active-employees', [EmployeeController::class,'getEmployees',]);
    Route::patch('/{employee}/status', [EmployeeController::class, 'updateStatus']);

    Route::get('/', [EmployeeController::class, 'index']);
    Route::post('/', [EmployeeController::class, 'store']);

    Route::get('/{employee}', [EmployeeController::class, 'show']);
    Route::put('/{employee}', [EmployeeController::class, 'update']);
    Route::patch('/{employee}', [EmployeeController::class, 'update']);
    Route::delete('/{employee}', [EmployeeController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| System Settings Routes
|--------------------------------------------------------------------------
*/

Route::prefix('system-settings')->group(function () {
    // Keep this route above /{id}.
    Route::get('/SystemInfo', [SystemSettingController::class, 'getSystemInfo']);

    Route::get('/', [SystemSettingController::class, 'index']);
    Route::post('/', [SystemSettingController::class, 'store']);
    Route::get('/{id}', [SystemSettingController::class, 'show']);
    Route::put('/{id}', [SystemSettingController::class, 'update']);
    Route::patch('/{id}', [SystemSettingController::class, 'update']);
    Route::delete('/{id}', [SystemSettingController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Protected Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('')->middleware('auth:sanctum')->group(function () {

    // Current authenticated user
    Route::get('/me', [AuthController::class, 'me']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Change email
    Route::post('/change-email', [AuthController::class, 'changeEmail']);

    // Change password
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    /*
    |--------------------------------------------------------------------------
    | Authenticated User Details
    |--------------------------------------------------------------------------
    */

    Route::prefix('user')->group(function () {
        Route::get('/', function (Request $request) {
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
});