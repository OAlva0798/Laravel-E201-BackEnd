<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    /**
     * Retrieve active companies.
     */
    public function getCompanies(): JsonResponse
    {
        try {
            $companies = Company::query()
                ->where('mco_status', 'Active')
                ->orderBy('mco_name', 'asc')
                ->get([
                    'mco_id',
                    'mco_reference',
                    'mco_name',
                    'mco_status',
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Active companies retrieved successfully.',
                'data' => $companies,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Failed to retrieve active companies.', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve companies.',
            ], 500);
        }
    }

    /**
     * Display all companies, with optional search and status filters.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'search' => ['sometimes', 'nullable', 'string', 'max:255'],
                'status' => [
                    'sometimes',
                    'nullable',
                    'in:Active,Inactive,Deleted',
                ],
                'include_deleted' => ['sometimes', 'boolean'],
            ]);

            $query = Company::query();

            if (!empty($validated['search'])) {
                $search = $validated['search'];

                $query->where(function ($q) use ($search) {
                    $q->where('mco_reference', 'LIKE', "%{$search}%")
                      ->orWhere('mco_name', 'LIKE', "%{$search}%");
                });
            }

            if (!empty($validated['status'])) {
                $query->where('mco_status', $validated['status']);
            }

            if (!$request->boolean('include_deleted')) {
                $query->where('mco_status', '!=', 'Deleted');
            }

            $companies = $query
                ->orderBy('mco_name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Companies retrieved successfully.',
                'data' => $companies,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
            Log::error('Failed to retrieve companies.', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve companies.',
            ], 500);
        }
    }

    /**
     * Store a newly created company.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'mco_name' => [
                    'required',
                    'string',
                    'max:255',
                ],
            ]);

            $company = DB::transaction(function () use ($validated) {
                $lastCompany = Company::query()
                    ->lockForUpdate()
                    ->orderByDesc('mco_id')
                    ->first();

                $nextId = $lastCompany
                    ? ((int) $lastCompany->mco_id + 1)
                    : 1;

                $year = now()->format('Y');

                $reference = sprintf(
                    'MCO%s-%05d',
                    $year,
                    $nextId
                );

                return Company::create([
                    'mco_reference' => $reference,
                    'mco_name' => $validated['mco_name'],
                    'mco_status' => 'Active',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Company created successfully.',
                'data' => $company,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
            Log::error('Failed to create company.', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create company.',
            ], 500);
        }
    }

    /**
     * Display a company.
     */
    public function show(Company $company): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Company retrieved successfully.',
            'data' => $company,
        ], 200);
    }

    /**
     * Update a company.
     */
    public function update(Request $request, Company $company): JsonResponse
    {
        try {
            $validated = $request->validate([
                'mco_reference' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:50',
                    'unique:maintenance_company,mco_reference,'
                        . $company->mco_id . ',mco_id',
                ],
                'mco_name' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255',
                ],
                'mco_status' => [
                    'sometimes',
                    'required',
                    'in:Active,Inactive,Deleted',
                ],
            ]);

            $company->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Company updated successfully.',
                'data' => $company->fresh(),
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
            Log::error('Failed to update company.', [
                'company_id' => $company->mco_id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update company.',
            ], 500);
        }
    }

    /**
     * Soft-delete a company by changing its status.
     */
    public function destroy(Company $company): JsonResponse
    {
        try {
            $company->update([
                'mco_status' => 'Deleted',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Company deleted successfully.',
                'data' => $company->fresh(),
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Failed to delete company.', [
                'company_id' => $company->mco_id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete company.',
            ], 500);
        }
    }
}
