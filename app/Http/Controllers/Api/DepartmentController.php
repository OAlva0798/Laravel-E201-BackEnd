<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function getDepartments(): JsonResponse
    {
        try {
            $departments = Department::where('md_status', 'Active')
                ->orderBy('md_name', 'asc')
                ->get([
                    'md_id',
                    'md_reference',
                    'md_center_reference',
                    'md_name',
                    'md_status',
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Active departments retrieved successfully.',
                'data' => $departments,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve departments.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a listing of all departments including center details.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Eager load center to make mc_name available
            $query = Department::with('center');

            // Search (including center name if requested)
            if ($request->filled('search')) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('md_reference', 'LIKE', "%{$search}%")
                      ->orWhere('md_center_reference', 'LIKE', "%{$search}%")
                      ->orWhere('md_name', 'LIKE', "%{$search}%")
                      ->orWhereHas('center', function ($centerQuery) use ($search) {
                          $centerQuery->where('mc_name', 'LIKE', "%{$search}%");
                      });
                });
            }

            // Filter by center reference
            if ($request->filled('center_reference')) {
                $query->where('md_center_reference', $request->center_reference);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('md_status', $request->status);
            }

            // Hide deleted records by default
            if (!$request->boolean('include_deleted')) {
                $query->where('md_status', '!=', 'Deleted');
            }

            $departments = $query
                ->orderBy('md_name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Departments retrieved successfully.',
                'data' => $departments
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve departments.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'md_center_reference' => [
                    'required',
                    'string',
                    'max:50'
                ],
                'md_name' => [
                    'required',
                    'string',
                    'max:255'
                ],
            ]);

            $department = DB::transaction(function () use ($validated) {
                $lastDepartment = Department::lockForUpdate()->orderBy('md_id', 'desc')->first();
                $nextId = $lastDepartment ? $lastDepartment->md_id + 1 : 1;
                
                $formattedId = str_pad($nextId, 5, '0', STR_PAD_LEFT); 
                $currentYear = date('Y');
                
                $generatedReference = "MD{$currentYear}-{$formattedId}";

                $dept = Department::create([
                    'md_reference' => $generatedReference,
                    'md_center_reference' => $validated['md_center_reference'],
                    'md_name' => $validated['md_name'],
                    'md_status' => 'Active',
                ]);

                return $dept->load('center');
            });

            return response()->json([
                'success' => true,
                'message' => 'Department created successfully.',
                'data' => $department
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create department.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified department by md_reference.
     */
    public function show(Department $department): JsonResponse
    {
        $department->load('center');

        return response()->json([
            'success' => true,
            'message' => 'Department retrieved successfully.',
            'data' => $department
        ], 200);
    }

    /**
     * Update the specified department by md_reference.
     */
    public function update(Request $request, Department $department): JsonResponse
    {
        try {
            $validated = $request->validate([
                'md_reference' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:50',
                    'unique:maintenance_department,md_reference,' . $department->md_id . ',md_id'
                ],
                'md_center_reference' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:50'
                ],
                'md_name' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255'
                ],
                'md_status' => [
                    'sometimes',
                    'required',
                    'in:Active,Inactive,Deleted'
                ],
            ]);

            $department->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Department updated successfully.',
                'data' => $department->fresh()->load('center')
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update department.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified department by md_reference (Soft Delete via status change).
     */
    public function destroy(Department $department): JsonResponse
    {
        try {
            $department->update([
                'md_status' => 'Deleted'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Department deleted successfully.',
                'data' => $department->fresh()->load('center')
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete department.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}