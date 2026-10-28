<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeMovementHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmployeeMovementHistoryController extends Controller
{
    /**
     * Movement history column => employees column.
     */
    private const EMPLOYEE_SYNC_MAP = [
        'eemh_company_reference'        => 'ee_company_reference',
        'eemh_center_reference'         => 'ee_center_reference',
        'eemh_department_reference'     => 'ee_department_reference',
        'eemh_position_reference'       => 'ee_position_reference',
        'eemh_position_level_reference' => 'ee_position_level_reference',
        'eemh_job_family'               => 'ee_job_family',
        'eemh_employment_status'        => 'ee_employment_status',
    ];

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
            $query->where('eemh_id_number', $validated['eemh_id_number']);
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
            'eemh_remarks' => ['nullable', 'string', 'max:255'],
            'eemh_employment_status' => ['nullable'],
            'eemh_date_effective' => ['nullable', 'date'],
        ]);

        $employeeSynced = false;

        $movement = DB::transaction(function () use ($validated, &$employeeSynced) {
            $movement = EmployeeMovementHistory::create($validated);
            $movement = $movement->fresh();

            $employeeSynced = $this->syncEmployeeIfCurrent($movement);

            return $movement;
        });

        return response()->json([
            'success' => true,
            'message' => 'Employee movement history created successfully.',
            'employee_synced' => $employeeSynced,
            'data' => $movement,
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
            'eemh_remarks' => ['sometimes', 'nullable', 'string', 'max:255'],
            'eemh_employment_status' => ['sometimes', 'nullable'],
            'eemh_date_effective' => ['sometimes', 'nullable', 'date'],
        ]);

        $oldEmployeeId = $movement->eemh_id_number;
        $employeeSynced = false;

        DB::transaction(function () use ($movement, $validated, $oldEmployeeId, &$employeeSynced) {
            $movement->update($validated);
            $movement = $movement->fresh();

            // If the record was moved to another employee, re-sync the old
            // employee from whatever record is now their current one.
            if ($movement->eemh_id_number !== $oldEmployeeId) {
                $this->syncEmployeeFromLatest($oldEmployeeId);
            }

            $employeeSynced = $this->syncEmployeeIfCurrent($movement);
        });

        return response()->json([
            'success' => true,
            'message' => 'Employee movement history updated successfully.',
            'employee_synced' => $employeeSynced,
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

    /**
     * Get the current (latest effective) movement record for an employee.
     */
    private function currentMovementFor(string $employeeIdNumber): ?EmployeeMovementHistory
    {
        return EmployeeMovementHistory::query()
            ->where('eemh_id_number', $employeeIdNumber)
            ->orderByDesc('eemh_date_effective')
            ->orderByDesc('eemh_id')
            ->first();
    }

    /**
     * Copy the movement values to the employee, but only when the given
     * movement is the employee's current record.
     */
    private function syncEmployeeIfCurrent(EmployeeMovementHistory $movement): bool
    {
        $current = $this->currentMovementFor($movement->eemh_id_number);

        if (!$current || (int) $current->eemh_id !== (int) $movement->eemh_id) {
            return false;
        }

        return $this->applyToEmployee($current);
    }

    /**
     * Re-sync an employee from their latest movement record (if any).
     */
    private function syncEmployeeFromLatest(string $employeeIdNumber): bool
    {
        $current = $this->currentMovementFor($employeeIdNumber);

        return $current ? $this->applyToEmployee($current) : false;
    }

    private function applyToEmployee(EmployeeMovementHistory $movement): bool
    {
        $data = [];

        foreach (self::EMPLOYEE_SYNC_MAP as $movementColumn => $employeeColumn) {
            $data[$employeeColumn] = $movement->{$movementColumn};
        }

        return Employee::where('ee_id_number', $movement->eemh_id_number)
            ->update($data) > 0;
    }
}