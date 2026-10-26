<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UsersAccount;
use App\Models\UserDetails;
use App\Mail\NewUserNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class UserDetailsController extends Controller
{
    /**
     * Display all Active and Inactive user details.
     */
    public function index(): JsonResponse
    {
        $users = UserDetails::with([
            'center',
            'department',
            'position',
        ])
            ->whereIn('ud_status', ['Active', 'Inactive','Deleted'])
            ->orderByDesc('ud_id')
            ->get();

        return response()->json($users);
    }

    /**
     * Display active user details only.
     */
    public function getUserDetails(): JsonResponse
    {
        $users = UserDetails::with([
            'center',
            'department',
            'position',
        ])
            ->where('ud_status', 'Active')
            ->orderBy('ud_firstname')
            ->orderBy('ud_lastname')
            ->get();

        return response()->json($users);
    }

    /**
     * Display a specific user.
     */
    public function show(UserDetails $userDetail): JsonResponse
    {
        $userDetail->load([
            'center',
            'department',
            'position',
        ]);

        return response()->json($userDetail);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ud_avatar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,gif,webp',
                'max:5120',
            ],

            'ud_esign' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,gif,webp',
                'max:5120',
            ],

            'ud_firstname' => [
                'required',
                'string',
                'max:50',
            ],

            'ud_middlename' => [
                'nullable',
                'string',
                'max:50',
            ],

            'ud_lastname' => [
                'required',
                'string',
                'max:50',
            ],

            'ud_email' => [
                'required',
                'email',
                'max:100',
                'unique:user_details,ud_email',
                'unique:users_account,uacc_email',
            ],

            'ud_center_reference' => [
                'nullable',
                'string',
                'max:50',
            ],

            'ud_department_reference' => [
                'nullable',
                'string',
                'max:50',
            ],

            'ud_position_reference' => [
                'nullable',
                'string',
                'max:50',
            ],

            'ud_center_head' => [
                'nullable',
                Rule::in(['Yes', 'No']),
            ],

            'ud_is_approver' => [
                'nullable',
                Rule::in(['Yes', 'No']),
            ],

            'ud_status' => [
                'nullable',
                Rule::in(['Active', 'Inactive', 'Deleted']),
            ],
        ], [
            'ud_email.required' => 'Email address is required.',
            'ud_email.unique' => 'This email address is already registered.',
        ]);

        $email = strtolower(trim($validated['ud_email']));

        // Generate a unique account reference.
        do {
            $reference = 'UACC' . strtoupper(Str::random(6));
        } while (
            UsersAccount::where('uacc_reference', $reference)->exists()
        );

        $generatedPassword = Str::random(12);

        $centerHead = $validated['ud_center_head'] ?? 'No';
        $isApprover = $validated['ud_is_approver'] ?? 'No';
        $status = $validated['ud_status'] ?? 'Active';

        $avatar = null;
        $esign = null;

        if ($request->hasFile('ud_avatar')) {
            $avatar = $this->storePublicFile(
                $request->file('ud_avatar'),
                'user_details/avatars',
                'avatar_'
            );
        }

        if ($request->hasFile('ud_esign')) {
            $esign = $this->storePublicFile(
                $request->file('ud_esign'),
                'user_details/esignatures',
                'esign_'
            );
        }

        try {
            [$user, $userDetails] = DB::transaction(
                function () use (
                    $reference,
                    $email,
                    $generatedPassword,
                    $status,
                    $centerHead,
                    $isApprover,
                    $avatar,
                    $esign,
                    $validated
                ) {
                    $user = UsersAccount::create([
                        'uacc_reference' => $reference,
                        'uacc_email' => $email,
                        'uacc_email_verified_at' => null,
                        'uacc_password' => Hash::make($generatedPassword),
                        'uacc_type' => 'User',
                        'uacc_status' => $status,
                        'uacc_failed_login_attempts' => 0,
                        'uacc_password_changed_at' => null,
                    ]);

                    $userDetails = UserDetails::create([
                        'ud_uniqid' => $reference,
                        'ud_avatar' => $avatar,
                        'ud_esign' => $esign,
                        'ud_firstname' => $validated['ud_firstname'],
                        'ud_middlename' => $validated['ud_middlename'] ?? null,
                        'ud_lastname' => $validated['ud_lastname'],
                        'ud_email' => $email,
                        'ud_center_reference' =>
                            $validated['ud_center_reference'] ?? null,
                        'ud_department_reference' =>
                            $validated['ud_department_reference'] ?? null,
                        'ud_position_reference' =>
                            $validated['ud_position_reference'] ?? null,
                        'ud_center_head' => $centerHead,
                        'ud_is_approver' => $isApprover,
                        'ud_status' => $status,
                    ]);

                    return [$user, $userDetails];
                }
            );
        } catch (Throwable $e) {
            // Remove uploaded files if database creation fails.
            $this->deletePublicFile($avatar);
            $this->deletePublicFile($esign);

            Log::error('Failed to create user account.', [
                'exception' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create user account.',
            ], 500);
        }

        // Send notification after the database transaction commits.
        $emailSent = false;

        try {
            Mail::to($user->uacc_email)->send(
                new NewUserNotification(
                    $user,
                    $generatedPassword
                )
            );

            $emailSent = true;
        } catch (Throwable $e) {
            report($e);
        }

        $userDetails->load([
            'center',
            'department',
            'position',
        ]);

        return response()->json([
            'success' => true,
            'message' => $emailSent
                ? 'User account created successfully. Please check the email address and verify the account.'
                : 'User account created successfully, but the verification email could not be sent.',
            'email_verification_required' => true,
            'email_verified' => false,
            'email_sent' => $emailSent,
            'user' => [
                'id' => $user->id,
                'uacc_reference' => $user->uacc_reference,
                'uacc_email' => $user->uacc_email,
                'uacc_type' => $user->uacc_type,
                'uacc_status' => $user->uacc_status,
                'uacc_email_verified_at' => $user->uacc_email_verified_at,
            ],
            'data' => $userDetails,
        ], 201);
    }

    /**
     * Update an existing user's personal information.
     *
     * Status-only changes should use updateStatus().
     */
    public function update(
        Request $request,
        UserDetails $userDetail
    ): JsonResponse {
        $validated = $request->validate([
            'ud_avatar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,gif,webp',
                'max:5120',
            ],

            'ud_esign' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,gif,webp',
                'max:5120',
            ],

            'ud_firstname' => [
                'sometimes',
                'required',
                'string',
                'max:50',
            ],

            'ud_middlename' => [
                'nullable',
                'string',
                'max:50',
            ],

            'ud_lastname' => [
                'sometimes',
                'required',
                'string',
                'max:50',
            ],

            'ud_email' => [
                'sometimes',
                'required',
                'email',
                'max:100',
                Rule::unique('user_details', 'ud_email')
                    ->ignore($userDetail->ud_id, 'ud_id'),
                Rule::unique('users_account', 'uacc_email')
                    ->ignore($userDetail->ud_uniqid, 'uacc_reference'),
            ],

            'ud_center_reference' => [
                'nullable',
                'string',
                'max:50',
            ],

            'ud_department_reference' => [
                'nullable',
                'string',
                'max:50',
            ],

            'ud_position_reference' => [
                'nullable',
                'string',
                'max:50',
            ],

            'ud_center_head' => [
                'nullable',
                Rule::in(['Yes', 'No']),
            ],

            'ud_is_approver' => [
                'nullable',
                Rule::in(['Yes', 'No']),
            ],

            // Retained for compatibility with existing edit forms.
            // Prefer updateStatus() for status-only requests.
            'ud_status' => [
                'sometimes',
                'required',
                Rule::in(['Active', 'Inactive', 'Deleted']),
            ],

            'ud_avatar_remove' => [
                'nullable',
                'boolean',
            ],

            'ud_esign_remove' => [
                'nullable',
                'boolean',
            ],
        ], [
            'ud_email.unique' =>
                'This email address is already registered to another user.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Avatar
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('ud_avatar')) {
            $newAvatar = $this->storePublicFile(
                $request->file('ud_avatar'),
                'user_details/avatars',
                'avatar_'
            );

            $oldAvatar = $userDetail->ud_avatar;

            $validated['ud_avatar'] = $newAvatar;
        } elseif ($request->boolean('ud_avatar_remove')) {
            $oldAvatar = $userDetail->ud_avatar;
            $validated['ud_avatar'] = null;
        } else {
            $oldAvatar = null;
            unset($validated['ud_avatar']);
        }

        /*
        |--------------------------------------------------------------------------
        | E-Signature
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('ud_esign')) {
            $newEsign = $this->storePublicFile(
                $request->file('ud_esign'),
                'user_details/esignatures',
                'esign_'
            );

            $oldEsign = $userDetail->ud_esign;

            $validated['ud_esign'] = $newEsign;
        } elseif ($request->boolean('ud_esign_remove')) {
            $oldEsign = $userDetail->ud_esign;
            $validated['ud_esign'] = null;
        } else {
            $oldEsign = null;
            unset($validated['ud_esign']);
        }

        unset(
            $validated['ud_avatar_remove'],
            $validated['ud_esign_remove']
        );

        try {
            DB::transaction(function () use (
                $userDetail,
                $validated
            ) {
                $userDetail->update($validated);

                // Keep the account email and status in sync when
                // their corresponding details are changed.
                $accountUpdates = [];

                if (array_key_exists('ud_email', $validated)) {
                    $accountUpdates['uacc_email'] =
                        strtolower(trim($validated['ud_email']));
                }

                if (array_key_exists('ud_status', $validated)) {
                    $accountUpdates['uacc_status'] =
                        $validated['ud_status'];
                }

                if ($accountUpdates !== []) {
                    UsersAccount::where(
                        'uacc_reference',
                        $userDetail->ud_uniqid
                    )->update($accountUpdates);
                }
            });
        } catch (Throwable $e) {
            // Clean up newly uploaded files if saving fails.
            if (isset($newAvatar)) {
                $this->deletePublicFile($newAvatar);
            }

            if (isset($newEsign)) {
                $this->deletePublicFile($newEsign);
            }

            Log::error('Failed to update user details.', [
                'user_detail_id' => $userDetail->ud_id,
                'exception' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user details.',
            ], 500);
        }

        // Delete replaced or removed files only after the database
        // update succeeds.
        if ($oldAvatar) {
            $this->deletePublicFile($oldAvatar);
        }

        if ($oldEsign) {
            $this->deletePublicFile($oldEsign);
        }

        $userDetail->refresh();

        $userDetail->load([
            'center',
            'department',
            'position',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User details updated successfully.',
            'data' => $userDetail,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Status
    |--------------------------------------------------------------------------
    |
    | Dedicated endpoint: PATCH /api/user-details/{userDetail}/status
    | Does not validate firstname, lastname, avatar, or e-signature.
    |
    */
public function updateStatus(
    Request $request,
    UserDetails $userDetail
): JsonResponse {
    $validated = $request->validate([
        'ud_status' => [
            'required',
            'string',
            Rule::in(['Active', 'Inactive', 'Deleted']),
        ],
    ]);

    $status = $validated['ud_status'];

    try {
        DB::transaction(function () use (
            $userDetail,
            $status
        ) {
            // Soft delete or update the user details status.
            $userDetail->update([
                'ud_status' => $status,
            ]);

            // Synchronize the linked account status.
            UsersAccount::where(
                'uacc_reference',
                $userDetail->ud_uniqid
            )->update([
                'uacc_status' => $status,
            ]);
        });

        $userDetail->refresh();

        $userDetail->load([
            'center',
            'department',
            'position',
        ]);

        $message = match ($status) {
            'Active' => 'User activated successfully.',
            'Inactive' => 'User deactivated successfully.',
            'Deleted' => 'User marked as deleted successfully.',
        };

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $userDetail,
        ]);
    } catch (Throwable $e) {
        Log::error('Failed to update user status.', [
            'user_detail_id' => $userDetail->ud_id,
            'status' => $status,
            'exception' => get_class($e),
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to update user status.',
        ], 500);
    }
}

    /**
     * Soft delete a user.
     *
     * The database record remains; status becomes "Deleted".
     */
    public function destroy(
        UserDetails $userDetail
    ): JsonResponse {
        try {
            DB::transaction(function () use ($userDetail) {
                $userDetail->update([
                    'ud_status' => 'Deleted',
                    'ud_avatar' => null,
                    'ud_esign' => null,
                ]);

                UsersAccount::where(
                    'uacc_reference',
                    $userDetail->ud_uniqid
                )->update([
                    'uacc_status' => 'Deleted',
                ]);
            });

            // Delete files after the database changes succeed.
            $this->deletePublicFile($userDetail->ud_avatar);
            $this->deletePublicFile($userDetail->ud_esign);

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to delete user details.', [
                'user_detail_id' => $userDetail->ud_id,
                'exception' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user.',
            ], 500);
        }
    }

    /**
     * Store an uploaded file inside public/.
     */
    private function storePublicFile(
        $file,
        string $relativeDirectory,
        string $prefix
    ): string {
        $directory = public_path($relativeDirectory);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = $prefix
            . date('YmdHis')
            . '_'
            . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8))
            . '.'
            . strtolower($file->getClientOriginalExtension());

        $file->move($directory, $filename);

        return '/' . $relativeDirectory . '/' . $filename;
    }

    /**
     * Delete a file from public/user_details/.
     */
    private function deletePublicFile(
        ?string $filePath
    ): void {
        if (!$filePath) {
            return;
        }

        $filePath = parse_url($filePath, PHP_URL_PATH);

        if (!$filePath) {
            return;
        }

        $filePath = ltrim($filePath, '/');

        // Only allow deletion inside public/user_details/.
        if (!str_starts_with($filePath, 'user_details/')) {
            return;
        }

        $fullPath = public_path($filePath);

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
