<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class EmployeeController extends Controller
{
    private const PROFILE_DIRECTORY = 'employees/profiles';

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Employee::query();

            if ($request->filled('search')) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($q) use ($search) {
                    $q->where('ee_id_number', 'like', "%{$search}%")
                        ->orWhere('ee_firstname', 'like', "%{$search}%")
                        ->orWhere('ee_middlename', 'like', "%{$search}%")
                        ->orWhere('ee_lastname', 'like', "%{$search}%")
                        ->orWhere('ee_email', 'like', "%{$search}%")
                        ->orWhere('ee_mobile_number', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('ee_status', $request->input('status'));
            }

            $filters = [
                'company_reference' => 'ee_company_reference',
                'center_reference' => 'ee_center_reference',
                'department_reference' => 'ee_department_reference',
                'position_reference' => 'ee_position_reference',
                'employment_status' => 'ee_employment_status',
            ];

            foreach ($filters as $parameter => $column) {
                if ($request->filled($parameter)) {
                    $query->where($column, $request->input($parameter));
                }
            }

            $employees = $query
                ->orderByDesc('ee_id')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Employees retrieved successfully.',
                'data' => $employees,
                'total' => $employees->count(),
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to retrieve employees.', [
                'exception' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve employees.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): JsonResponse
    {
        $profile = null;

        try {
            $validated = $request->validate(
                array_merge($this->rules(), [
                    'ee_id_number' => [
                        'required',
                        'string',
                        'max:50',
                        'unique:employees,ee_id_number',
                    ],
                    'ee_email' => [
                        'nullable',
                        'email',
                        'max:255',
                        'unique:employees,ee_email',
                    ],
                ])
            );


            // This flag is used only by the frontend.
            unset($validated['ee_profile_remove']);

            if ($request->hasFile('ee_profile')) {
                $directory = $this->profileDirectory(
                    $validated['ee_id_number']
                );

                $profile = $this->storePublicFile(
                    $request->file('ee_profile'),
                    $directory,
                    'profile_'
                );

                $validated['ee_profile'] = $profile;
            } else {
                $validated['ee_profile'] = null;
            }

            $employee = DB::transaction(function () use ($validated) {
                return Employee::create($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully.',
                'data' => $employee->fresh(),
            ], 201);
        } catch (ValidationException $e) {
            $this->deletePublicFile($profile);

            throw $e;
        } catch (Throwable $e) {
            $this->deletePublicFile($profile);

            Log::error('Failed to create employee.', [
                'exception' => get_class($e),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => app()->environment('local')
                    ? $e->getMessage()
                    : 'Failed to create employee. Check the application logs.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(string $id): JsonResponse
    {
        try {
            $employee = Employee::find($id);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Employee retrieved successfully.',
                'data' => $employee,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to retrieve employee.', [
                'employee_id' => $id,
                'exception' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve employee.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, string $id): JsonResponse
    {
        $newProfile = null;

        try {
            $employee = Employee::find($id);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found.',
                ], 404);
            }

            $validated = $request->validate(
                array_merge($this->rules(), [
                    'ee_id_number' => [
                        'sometimes',
                        'required',
                        'string',
                        'max:50',
                        Rule::unique('employees', 'ee_id_number')
                            ->ignore($employee->ee_id, 'ee_id'),
                    ],
                    'ee_email' => [
                        'sometimes',
                        'nullable',
                        'email',
                        'max:255',
                        Rule::unique('employees', 'ee_email')
                            ->ignore($employee->ee_id, 'ee_id'),
                    ],
                    'ee_profile_remove' => [
                        'sometimes',
                        'boolean',
                    ],
                ])
            );

            $oldProfile = $employee->ee_profile;
            $removeProfile = $request->boolean('ee_profile_remove');

            unset($validated['ee_profile_remove']);

            if ($request->hasFile('ee_profile')) {
                $directory = $this->profileDirectory(
                    $validated['ee_id_number'] ?? $employee->ee_id_number
                );

                $newProfile = $this->storePublicFile(
                    $request->file('ee_profile'),
                    $directory,
                    'profile_'
                );

                $validated['ee_profile'] = $newProfile;
            } elseif ($removeProfile) {
                $validated['ee_profile'] = null;
            } else {
                // Preserve the existing image when no image action is sent.
                unset($validated['ee_profile']);
            }

            DB::transaction(function () use ($employee, $validated) {
                $employee->update($validated);
            });

            $profileChanged = array_key_exists('ee_profile', $validated)
                && $validated['ee_profile'] !== $oldProfile;

            if ($profileChanged) {
                $this->deletePublicFile($oldProfile);
            }

            return response()->json([
                'success' => true,
                'message' => 'Employee updated successfully.',
                'data' => $employee->fresh(),
            ]);
        } catch (ValidationException $e) {
            $this->deletePublicFile($newProfile);

            throw $e;
        } catch (Throwable $e) {
            $this->deletePublicFile($newProfile);

            Log::error('Failed to update employee.', [
                'employee_id' => $id,
                'exception' => get_class($e),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => app()->environment('local')
                    ? $e->getMessage()
                    : 'Failed to update employee. Check the application logs.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        Request $request,
        string $id
    ): JsonResponse {
        $validated = $request->validate([
            'ee_status' => [
                'required',
                'string',
                Rule::in(['Active', 'Inactive', 'Deleted']),
            ],
        ]);

        $employee = Employee::where('ee_id', $id)->first();

        if (!$employee) {
            return response()->json([
                'message' => 'Employee not found.',
            ], 404);
        }

        $employee->ee_status = $validated['ee_status'];
        $employee->save();

        return response()->json([
            'message' => $validated['ee_status'] === 'Deleted'
                ? 'Employee marked as deleted successfully.'
                : 'Employee status updated successfully.',
            'data' => $employee->fresh(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(string $id): JsonResponse
    {
        try {
            $employee = Employee::find($id);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found.',
                ], 404);
            }

            $oldProfile = $employee->ee_profile;

            DB::transaction(function () use ($employee) {
                $employee->delete();
            });

            $this->deletePublicFile($oldProfile);

            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully.',
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to delete employee.', [
                'employee_id' => $id,
                'exception' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete employee.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */

    private function rules(): array
    {
        return [
            // Profile Image
            'ee_profile' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,gif,webp',
                'max:5120',
            ],

            // Personal Information
            'ee_firstname' => [
                'required',
                'string',
                'max:100',
            ],
            'ee_middlename' => [
                'nullable',
                'string',
                'max:100',
            ],
            'ee_lastname' => [
                'required',
                'string',
                'max:100',
            ],
            'ee_gender' => [
                'nullable',
                'string',
                'max:255',
            ],
            'ee_birth_date' => [
                'nullable',
                'date',
            ],
            'ee_civil_status' => [
                'nullable',
                'string',
                'max:255',
            ],

            // Contact Information
            'ee_email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'ee_mobile_number' => [
                'nullable',
                'string',
                'max:30',
            ],
            'ee_telephone' => [
                'nullable',
                'string',
                'max:30',
            ],

            // Government Information
            'ee_sss' => [
                'nullable',
                'string',
                'max:30',
            ],
            'ee_tinid' => [
                'nullable',
                'string',
                'max:30',
            ],
            'ee_bloodtype' => [
                'nullable',
                'string',
                'max:10',
            ],

            // Emergency Contact
            'ee_ecname' => [
                'nullable',
                'string',
                'max:150',
            ],
            'ee_eccontact' => [
                'nullable',
                'string',
                'max:30',
            ],
            'ee_ecaddress' => [
                'nullable',
                'string',
                'max:150',
            ],

            // Organizational Assignment
            'ee_company_reference' => [
                'nullable',
                'string',
                'max:255',
            ],
            'ee_center_reference' => [
                'nullable',
                'string',
                'max:255',
            ],
            'ee_department_reference' => [
                'nullable',
                'string',
                'max:255',
            ],
            'ee_job_family' => [
                'nullable',
                'string',
                'max:255',
            ],
            'ee_position_level_reference' => [
                'nullable',
                'string',
                'max:255',
            ],
            'ee_position_reference' => [
                'nullable',
                'string',
                'max:255',
            ],
            'ee_employment_status' => [
                'nullable',
                'string',
                'max:255',
            ],

            // Employment Dates
            'ee_date_hired' => [
                'nullable',
                'date',
            ],
            'ee_date_hired_tdg' => [
                'nullable',
                'date',
            ],
            'ee_date_contractual_start' => [
                'nullable',
                'date',
            ],
            'ee_date_contractual_end' => [
                'nullable',
                'date',
                'after_or_equal:ee_date_contractual_start',
            ],

            // Biometrics and Record Status
            'ee_id_biometrics' => [
                'nullable',
                'string',
                'max:255',
            ],
            'ee_status' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['Active', 'Inactive', 'Archived']),
            ],

            // Audit Information
            'ee_created_by' => [
                'nullable',
                'string',
                'max:255',
            ],

            // Frontend-only flag
            'ee_profile_remove' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Profile Directory
    |--------------------------------------------------------------------------
    */

    private function profileDirectory(string $employeeIdNumber): string
    {
        $employeeIdNumber = trim($employeeIdNumber);

        if (
            $employeeIdNumber === '' ||
            $employeeIdNumber === '.' ||
            $employeeIdNumber === '..' ||
            preg_match('/[\/\\\\\x00-\x1F\x7F]/', $employeeIdNumber)
        ) {
            throw new RuntimeException(
                'Invalid employee ID number for profile storage.'
            );
        }

        return self::PROFILE_DIRECTORY . '/' . $employeeIdNumber;
    }

    /*
    |--------------------------------------------------------------------------
    | Store Public File
    |--------------------------------------------------------------------------
    */

    private function storePublicFile(
        $file,
        string $relativeDirectory,
        string $prefix
    ): string {
        $relativeDirectory = trim(
            str_replace('\\', '/', $relativeDirectory),
            '/'
        );

        if (!str_starts_with(
            $relativeDirectory,
            self::PROFILE_DIRECTORY . '/'
        )) {
            throw new RuntimeException(
                'Invalid employee profile directory.'
            );
        }

        $rootPath = public_path(self::PROFILE_DIRECTORY);

        if (
            !is_dir($rootPath) &&
            !mkdir($rootPath, 0755, true) &&
            !is_dir($rootPath)
        ) {
            throw new RuntimeException(
                'Unable to create the employee profile directory.'
            );
        }

        $root = realpath($rootPath);

        if ($root === false) {
            throw new RuntimeException(
                'Unable to resolve the employee profile directory.'
            );
        }

        $directory = public_path($relativeDirectory);

        if (
            !is_dir($directory) &&
            !mkdir($directory, 0755, true) &&
            !is_dir($directory)
        ) {
            throw new RuntimeException(
                'Unable to create the employee profile directory.'
            );
        }

        $realDirectory = realpath($directory);

        if (
            $realDirectory === false ||
            !str_starts_with(
                $realDirectory,
                $root . DIRECTORY_SEPARATOR
            )
        ) {
            throw new RuntimeException(
                'Invalid employee profile storage path.'
            );
        }

        $extension = strtolower($file->extension() ?: 'jpg');

        $filename = $prefix
            . now()->format('YmdHis')
            . '_'
            . strtoupper(Str::random(8))
            . '.'
            . $extension;

        $file->move($realDirectory, $filename);

        return '/' . $relativeDirectory . '/' . $filename;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Public File
    |--------------------------------------------------------------------------
    */

    private function deletePublicFile(?string $filePath): void
    {
        if (!$filePath || trim($filePath) === '') {
            return;
        }

        $filePath = trim($filePath);

        if (preg_match('/^(https?:\/\/|data:|blob:)/i', $filePath)) {
            return;
        }

        $parsedPath = parse_url($filePath, PHP_URL_PATH);

        if (!$parsedPath) {
            return;
        }

        $parsedPath = ltrim(
            str_replace('\\', '/', $parsedPath),
            '/'
        );

        // Support legacy /storage/... URLs.
        if (str_starts_with($parsedPath, 'storage/')) {
            $parsedPath = substr($parsedPath, strlen('storage/'));
        }

        $profilePrefix = self::PROFILE_DIRECTORY . '/';

        if (!str_starts_with($parsedPath, $profilePrefix)) {
            return;
        }

        $baseDirectory = realpath(
            public_path(self::PROFILE_DIRECTORY)
        );

        $realFilePath = realpath(public_path($parsedPath));

        if (
            $baseDirectory === false ||
            $realFilePath === false ||
            !is_file($realFilePath)
        ) {
            return;
        }

        if (!str_starts_with(
            $realFilePath,
            $baseDirectory . DIRECTORY_SEPARATOR
        )) {
            return;
        }

        try {
            if (!@unlink($realFilePath)) {
                Log::warning(
                    'Failed to delete employee profile image.',
                    ['path' => $parsedPath]
                );
            }
        } catch (Throwable $e) {
            Log::warning('Failed to delete employee profile image.', [
                'path' => $parsedPath,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
