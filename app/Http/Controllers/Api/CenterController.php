<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Center;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class CenterController extends Controller
{
    /**
     * Retrieve active centers.
     */
    public function getCenters(): JsonResponse
    {
        try {
            $centers = Center::where('mc_status', 'Active')
                ->orderBy('mc_name', 'asc')
                ->get([
                    'mc_id',
                    'mc_reference',
                    'mc_name',
                    'mc_head_uaccid',
                    'mc_head_position',
                    'mc_status',
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Active centers retrieved successfully.',
                'data' => $centers,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve centers.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a listing of all centers.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Center::query();

            // Search by reference, name, head uaccid, or head position
            if ($request->filled('search')) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('mc_reference', 'LIKE', "%{$search}%")
                      ->orWhere('mc_name', 'LIKE', "%{$search}%")
                      ->orWhere('mc_head_uaccid', 'LIKE', "%{$search}%")
                      ->orWhere('mc_head_position', 'LIKE', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('mc_status', $request->status);
            }

            // Hide deleted records by default
            if (!$request->boolean('include_deleted')) {
                $query->where('mc_status', '!=', 'Deleted');
            }

            $centers = $query
                ->orderBy('mc_name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Centers retrieved successfully.',
                'data' => $centers
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve centers.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created center.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'mc_name' => [
                    'required',
                    'string',
                    'max:255'
                ],
                'mc_head_uaccid' => [
                    'nullable',
                    'string',
                    'max:50'
                ],
                'mc_head_position' => [
                    'nullable',
                    'string',
                    'max:255'
                ],
            ]);

            $center = DB::transaction(function () use ($validated) {
                $lastCenter = Center::lockForUpdate()->orderBy('mc_id', 'desc')->first();
                $nextId = $lastCenter ? $lastCenter->mc_id + 1 : 1;
                
                $formattedId = str_pad($nextId, 5, '0', STR_PAD_LEFT); 
                $currentYear = date('Y');
                
                $generatedReference = "MC{$currentYear}-{$formattedId}";

                return Center::create([
                    'mc_reference' => $generatedReference,
                    'mc_name' => $validated['mc_name'],
                    'mc_head_uaccid' => $validated['mc_head_uaccid'] ?? null,
                    'mc_head_position' => $validated['mc_head_position'] ?? null,
                    'mc_status' => 'Active',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Center created successfully.',
                'data' => $center
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
                'message' => 'Failed to create center.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified center by mc_reference.
     */
    public function show(Center $center): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Center retrieved successfully.',
            'data' => $center
        ], 200);
    }

    /**
     * Update the specified center by mc_reference.
     */
    public function update(Request $request, Center $center): JsonResponse
    {
        try {
            $validated = $request->validate([
                'mc_reference' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:50',
                    'unique:maintenance_center,mc_reference,' . $center->mc_id . ',mc_id'
                ],
                'mc_name' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255'
                ],
                'mc_head_uaccid' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:50'
                ],
                'mc_head_position' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:255'
                ],
                'mc_status' => [
                    'sometimes',
                    'required',
                    'in:Active,Inactive,Deleted'
                ],
            ]);

            $center->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Center updated successfully.',
                'data' => $center->fresh()
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
                'message' => 'Failed to update center.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified center by mc_reference (Soft Delete via status change).
     */
    public function destroy(Center $center): JsonResponse
    {
        try {
            $center->update([
                'mc_status' => 'Deleted'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Center deleted successfully.',
                'data' => $center->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete center.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}