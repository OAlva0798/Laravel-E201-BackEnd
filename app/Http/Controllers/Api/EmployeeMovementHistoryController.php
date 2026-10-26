<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeMovementHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class EmployeeMovementHistoryController extends Controller
{
    /**
     * List movement history.
     *
     * Optional query parameters:
     * ?eemh_id_number=EMPLOYEE-ID
     * ?per_page=10
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'eemh_id_number' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $query = EmployeeMovementHistory::query()
            ->orderByDesc('eemh_date_effective')
            ->orderByDesc('eemh_id');

        if (!empty($validated['eemh_id_number'])) {
            $query->where(
                'eemh_id_number',
                $validated['eemh_id_number']
            );
        }

        $perPage = $validated['per_page'] ?? 10;

        return response()->json([
            'success' => true,
            'message' => 'Employee movement history retrieved successfully.',
            'data' => $query->paginate($perPage),
        ]);
    }

    /**
     * Create a movement history record.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'eemh_id_number' => [
                'required',
                'string',
                'max:255',
                Rule::exists('employees', 'ee_id_number'),
            ],
            'eemh_company_reference' => ['nullable', 'string', 'max:255'],
            'eemh_center_reference' => ['nullable', 'string', 'max:255'],
            'eemh_department_reference' => ['nullable', 'string', 'max:255'],
            'eemh_position_reference' => ['nullable', 'string', 'max:255'],
            'eemh_position_level_reference' => ['nullable', 'string', 'max:255'],
            'eemh_job_family' => ['nullable', 'string', 'max:255'],
            'eemh_employment_status' => [
                'nullable',
            ],
            'eemh_date_effective' => ['nullable', 'date'],
        ]);

        $movement = EmployeeMovementHistory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Employee movement history created successfully.',
            'data' => $movement->fresh(),
        ], 201);
    }

    /**
     * View one movement history record.
     */
    public function show(string $id): JsonResponse
    {
        $movement = EmployeeMovementHistory::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Employee movement history retrieved successfully.',
            'data' => $movement,
        ]);
    }

    /**
     * Update a movement history record.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $movement = EmployeeMovementHistory::findOrFail($id);

        $validated = $request->validate([
            'eemh_id_number' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::exists('employees', 'ee_id_number'),
            ],
            'eemh_company_reference' => ['sometimes', 'nullable', 'string', 'max:255'],
            'eemh_center_reference' => ['sometimes', 'nullable', 'string', 'max:255'],
            'eemh_department_reference' => ['sometimes', 'nullable', 'string', 'max:255'],
            'eemh_position_reference' => ['sometimes', 'nullable', 'string', 'max:255'],
            'eemh_position_level_reference' => ['sometimes', 'nullable', 'string', 'max:255'],
            'eemh_job_family' => ['sometimes', 'nullable', 'string', 'max:255'],
            'eemh_employment_status' => [
                'sometimes',
                'nullable',
            ],
            'eemh_date_effective' => ['sometimes', 'nullable', 'date'],
        ]);

        $movement->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Employee movement history updated successfully.',
            'data' => $movement->fresh(),
        ]);
    }

    /**
     * Delete one movement history record.
     */
    public function destroy(string $id): JsonResponse
    {
        $movement = EmployeeMovementHistory::findOrFail($id);

        $movement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employee movement history deleted successfully.',
        ]);
    }
}