<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use App\Models\UsersAccount;
use App\Models\UserDetails;
use App\Mail\NewUserNotification;

class UserDetailsController extends Controller
{
    /**
     * Display all Active and Inactive user details.
     */
    public function index()
    {
        $users = UserDetails::with([
            'center',
            'department',
            'position',
        ])
            ->whereIn('ud_status', ['Active', 'Inactive'])
            ->orderBy('ud_id', 'desc')
            ->get();

        return response()->json($users);
    }

    /**
     * Display active user details only.
     */
    public function getUserDetails()
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
    public function show(UserDetails $userDetail)
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Avatar
            'ud_avatar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,gif,webp',
                'max:5120',
            ],

            // E-Signature
            'ud_esign' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,gif,webp',
                'max:5120',
            ],

            // Personal Information
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

            // Work Assignment
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

            // Permissions
            'ud_center_head' => [
                'nullable',
                Rule::in(['Yes', 'No']),
            ],

            'ud_is_approver' => [
                'nullable',
                Rule::in(['Yes', 'No']),
            ],

            // Status
            'ud_status' => [
                'nullable',
                Rule::in(['Active', 'Inactive', 'Deleted']),
            ],
        ], [
            'ud_email.required' =>
            'Email address is required.',

            'ud_email.unique' =>
            'This email address is already registered.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Normalize Email
        |--------------------------------------------------------------------------
        */

        $email = strtolower(
            trim($validated['ud_email'])
        );

        /*
        |--------------------------------------------------------------------------
        | Generate Unique Account Reference
        |--------------------------------------------------------------------------
        */

        do {
            $reference = 'UACC' . strtoupper(
                Str::random(6)
            );
        } while (
            UsersAccount::where(
                'uacc_reference',
                $reference
            )->exists()
        );

        /*
        |--------------------------------------------------------------------------
        | Generate Temporary Password
        |--------------------------------------------------------------------------
        */

        $generatedPassword = Str::random(12);

        /*
        |--------------------------------------------------------------------------
        | Default Values
        |--------------------------------------------------------------------------
        */

        $centerHead =
            $validated['ud_center_head'] ?? 'No';

        $isApprover =
            $validated['ud_is_approver'] ?? 'No';

        $status =
            $validated['ud_status'] ?? 'Active';

        /*
        |--------------------------------------------------------------------------
        | Store Avatar
        |--------------------------------------------------------------------------
        */

        $avatar = null;

        if ($request->hasFile('ud_avatar')) {
            $avatar = $this->storePublicFile(
                $request->file('ud_avatar'),
                'user_details/avatars',
                'avatar_'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Store E-Signature
        |--------------------------------------------------------------------------
        */

        $esign = null;

        if ($request->hasFile('ud_esign')) {
            $esign = $this->storePublicFile(
                $request->file('ud_esign'),
                'user_details/esignatures',
                'esign_'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Create User Account
        |--------------------------------------------------------------------------
        */

        $user = UsersAccount::create([
            'uacc_reference' =>
            $reference,

            'uacc_email' =>
            $email,

            'uacc_email_verified_at' =>
            null,

            'uacc_password' =>
            Hash::make(
                $generatedPassword
            ),

            'uacc_type' =>
            'User',

            'uacc_status' =>
            $status,

            'uacc_failed_login_attempts' =>
            0,

            'uacc_password_changed_at' =>
            null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create User Details
        |--------------------------------------------------------------------------
        */

        $userDetails = UserDetails::create([
            'ud_uniqid' =>
            $reference,

            'ud_avatar' =>
            $avatar,

            'ud_esign' =>
            $esign,

            'ud_firstname' =>
            $validated['ud_firstname'],

            'ud_middlename' =>
            $validated['ud_middlename'] ?? null,

            'ud_lastname' =>
            $validated['ud_lastname'],

            'ud_email' =>
            $email,

            'ud_center_reference' =>
            $validated['ud_center_reference'] ?? null,

            'ud_department_reference' =>
            $validated['ud_department_reference'] ?? null,

            'ud_position_reference' =>
            $validated['ud_position_reference'] ?? null,

            'ud_center_head' =>
            $centerHead,

            'ud_is_approver' =>
            $isApprover,

            'ud_status' =>
            $status,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Send New User Notification
        |--------------------------------------------------------------------------
        */

        $emailSent = false;

        try {
            Mail::to($user->uacc_email)->send(
                new NewUserNotification(
                    $user,
                    $generatedPassword
                )
            );

            $emailSent = true;
        } catch (\Throwable $e) {
            report($e);
        }

        /*
        |--------------------------------------------------------------------------
        | Load Relationships
        |--------------------------------------------------------------------------
        */

        $userDetails->load([
            'center',
            'department',
            'position',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
            true,

            'message' =>
            $emailSent
                ? 'User account created successfully. Please check the email address and verify the account.'
                : 'User account created successfully, but the verification email could not be sent.',

            'email_verification_required' =>
            true,

            'email_verified' =>
            false,

            'email_sent' =>
            $emailSent,

            'user' => [
                'id' =>
                $user->id,

                'uacc_reference' =>
                $user->uacc_reference,

                'uacc_email' =>
                $user->uacc_email,

                'uacc_type' =>
                $user->uacc_type,

                'uacc_status' =>
                $user->uacc_status,

                'uacc_email_verified_at' =>
                $user->uacc_email_verified_at,
            ],

            'data' =>
            $userDetails,
        ], 201);
    }

    /**
     * Update an existing user.
     */
    public function update(
        Request $request,
        UserDetails $userDetail
    ) {
        $validated = $request->validate([
            // Avatar
            'ud_avatar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,gif,webp',
                'max:5120',
            ],

            // E-Signature
            'ud_esign' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,gif,webp',
                'max:5120',
            ],

            // Personal Information
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
                'nullable',
                'email',
                'max:100',
                Rule::unique(
                    'user_details',
                    'ud_email'
                )->ignore(
                    $userDetail->ud_id,
                    'ud_id'
                ),
            ],

            // Work Assignment
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

            // Permissions
            'ud_center_head' => [
                'nullable',
                Rule::in(['Yes', 'No']),
            ],

            'ud_is_approver' => [
                'nullable',
                Rule::in(['Yes', 'No']),
            ],

            // Status
            'ud_status' => [
                'nullable',
                Rule::in([
                    'Active',
                    'Inactive',
                    'Deleted',
                ]),
            ],

            // Remove flags
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
            $this->deletePublicFile(
                $userDetail->ud_avatar
            );

            $validated['ud_avatar'] =
                $this->storePublicFile(
                    $request->file('ud_avatar'),
                    'user_details/avatars',
                    'avatar_'
                );
        } elseif (
            $request->boolean('ud_avatar_remove')
        ) {
            $this->deletePublicFile(
                $userDetail->ud_avatar
            );

            $validated['ud_avatar'] = null;
        } else {
            unset($validated['ud_avatar']);
        }

        /*
        |--------------------------------------------------------------------------
        | E-Signature
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('ud_esign')) {
            $this->deletePublicFile(
                $userDetail->ud_esign
            );

            $validated['ud_esign'] =
                $this->storePublicFile(
                    $request->file('ud_esign'),
                    'user_details/esignatures',
                    'esign_'
                );
        } elseif (
            $request->boolean('ud_esign_remove')
        ) {
            $this->deletePublicFile(
                $userDetail->ud_esign
            );

            $validated['ud_esign'] = null;
        } else {
            unset($validated['ud_esign']);
        }

        /*
        |--------------------------------------------------------------------------
        | Remove Frontend-only Fields
        |--------------------------------------------------------------------------
        */

        unset(
            $validated['ud_avatar_remove'],
            $validated['ud_esign_remove']
        );

        /*
        |--------------------------------------------------------------------------
        | Update User Details
        |--------------------------------------------------------------------------
        */

        $userDetail->update($validated);

        $userDetail->refresh();

        $userDetail->load([
            'center',
            'department',
            'position',
        ]);

        return response()->json([
            'success' => true,
            'message' =>
            'User details updated successfully.',
            'data' => $userDetail,
        ]);
    }

    /**
     * Soft delete a user.
     *
     * The database record remains;
     * status becomes "Deleted".
     */
    public function destroy(UserDetails $userDetail)
    {
        if ($userDetail->ud_avatar) {
            $this->deletePublicFile(
                $userDetail->ud_avatar
            );
        }

        if ($userDetail->ud_esign) {
            $this->deletePublicFile(
                $userDetail->ud_esign
            );
        }

        $userDetail->update([
            'ud_status' => 'Deleted',
            'ud_avatar' => null,
            'ud_esign' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' =>
            'User status updated to deleted successfully.',
        ]);
    }

    /**
     * Store an uploaded file inside public/.
     */
    private function storePublicFile(
        $file,
        string $relativeDirectory,
        string $prefix
    ): string {
        $directory = public_path(
            $relativeDirectory
        );

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename =
            $prefix .
            date('YmdHis') .
            '_' .
            strtoupper(
                substr(
                    bin2hex(random_bytes(4)),
                    0,
                    8
                )
            ) .
            '.' .
            strtolower(
                $file->getClientOriginalExtension()
            );

        $file->move(
            $directory,
            $filename
        );

        return '/' .
            $relativeDirectory .
            '/' .
            $filename;
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

        $filePath = parse_url(
            $filePath,
            PHP_URL_PATH
        );

        if (!$filePath) {
            return;
        }

        $filePath = ltrim(
            $filePath,
            '/'
        );

        // Only allow files inside public/user_details/
        if (!str_starts_with(
            $filePath,
            'user_details/'
        )) {
            return;
        }

        $fullPath = public_path(
            $filePath
        );

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
