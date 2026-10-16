<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class PositionController extends Controller
{
    /**
     * Get a listing of active positions for dropdown selection.
     */
    
    public function getPositions(): JsonResponse
    {
        try {
            $positions = Position::where('mp_status', 'Active')
                ->orderBy('mp_name', 'asc')
                ->get([
                    'mp_id',
                    'mp_reference',
                    'mp_department_reference',
                    'mp_name',
                    'mp_status',
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Active positions retrieved successfully.',
                'data' => $positions,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve positions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a listing of all positions including department details.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Position::with('department');

            // Search (including department name if requested)
            if ($request->filled('search')) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('mp_reference', 'LIKE', "%{$search}%")
                      ->orWhere('mp_department_reference', 'LIKE', "%{$search}%")
                      ->orWhere('mp_name', 'LIKE', "%{$search}%")
                      ->orWhereHas('department', function ($deptQuery) use ($search) {
                          $deptQuery->where('md_name', 'LIKE', "%{$search}%");
                      });
                });
            }

            // Filter by department reference
            if ($request->filled('department_reference')) {
                $query->where('mp_department_reference', $request->department_reference);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('mp_status', $request->status);
            }

            // Hide deleted records by default
            if (!$request->boolean('include_deleted')) {
                $query->where('mp_status', '!=', 'Deleted');
            }

            $positions = $query
                ->orderBy('mp_name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Positions retrieved successfully.',
                'data' => $positions
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve positions.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created position.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'mp_department_reference' => [
                    'required',
                    'string',
                    'max:50'
                ],
                'mp_name' => [
                    'required',
                    'string',
                    'max:255'
                ],
            ]);

            $position = DB::transaction(function () use ($validated) {
                $lastPosition = Position::lockForUpdate()->orderBy('mp_id', 'desc')->first();
                $nextId = $lastPosition ? $lastPosition->mp_id + 1 : 1;
                
                $formattedId = str_pad($nextId, 5, '0', STR_PAD_LEFT); 
                $currentYear = date('Y');
                
                $generatedReference = "MP{$currentYear}-{$formattedId}";

                $newPosition = Position::create([
                    'mp_reference' => $generatedReference,
                    'mp_department_reference' => $validated['mp_department_reference'],
                    'mp_name' => $validated['mp_name'],
                    'mp_status' => 'Active',
                ]);

                return $newPosition->load('department');
            });

            return response()->json([
                'success' => true,
                'message' => 'Position created successfully.',
                'data' => $position
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
                'message' => 'Failed to create position.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified position by mp_reference.
     */
    public function show(Position $position): JsonResponse
    {
        $position->load('department');

        return response()->json([
            'success' => true,
            'message' => 'Position retrieved successfully.',
            'data' => $position
        ], 200);
    }

    /**
     * Update the specified position by mp_reference.
     */
    public function update(Request $request, Position $position): JsonResponse
    {
        try {
            $validated = $request->validate([
                'mp_reference' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:50',
                    'unique:maintenance_position,mp_reference,' . $position->mp_id . ',mp_id'
                ],
                'mp_department_reference' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:50'
                ],
                'mp_name' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255'
                ],
                'mp_status' => [
                    'sometimes',
                    'required',
                    'in:Active,Inactive,Deleted'
                ],
            ]);

            $position->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Position updated successfully.',
                'data' => $position->fresh()->load('department')
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
                'message' => 'Failed to update position.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified position by mp_reference (Soft Delete via status change).
     */
    public function destroy(Position $position): JsonResponse
    {
        try {
            $position->update([
                'mp_status' => 'Deleted'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Position deleted successfully.',
                'data' => $position->fresh()->load('department')
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete position.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}