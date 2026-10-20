<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PositionLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PositionLevelController extends Controller
{
    /**
     * Retrieve active position levels.
     */
    public function getActivePositionLevels(): JsonResponse
    {
        try {
            $positionLevels = PositionLevel::query()
                ->where('mpl_status', 'Active')
                ->orderBy('mpl_name')
                ->get([
                    'mpl_id',
                    'mpl_reference',
                    'mpl_name',
                    'mpl_status',
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Active position levels retrieved successfully.',
                'data' => $positionLevels,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to retrieve active position levels.', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve active position levels.',
            ], 500);
        }
    }

    /**
     * Display position levels with optional search and status filters.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'search' => ['sometimes', 'nullable', 'string', 'max:255'],
                'status' => [
                    'sometimes',
                    'nullable',
                    Rule::in(['Active', 'Inactive', 'Deleted']),
                ],
                'include_deleted' => ['sometimes', 'boolean'],
            ]);

            $query = PositionLevel::query();

            if (!empty($validated['search'])) {
                $search = trim($validated['search']);

                $query->where(function ($q) use ($search) {
                    $q->where('mpl_reference', 'like', "%{$search}%")
                        ->orWhere('mpl_name', 'like', "%{$search}%");
                });
            }

            if (!empty($validated['status'])) {
                $query->where('mpl_status', $validated['status']);
            } elseif (!$request->boolean('include_deleted')) {
                $query->where('mpl_status', '!=', 'Deleted');
            }

            $positionLevels = $query
                ->orderBy('mpl_name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Position levels retrieved successfully.',
                'data' => $positionLevels,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Failed to retrieve position levels.', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve position levels.',
            ], 500);
        }
    }

    /**
     * Create a position level.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'mpl_name' => [
                    'required',
                    'string',
                    'max:255',
                ],
            ]);

            $positionLevel = DB::transaction(function () use ($validated) {
                $lastPositionLevel = PositionLevel::query()
                    ->lockForUpdate()
                    ->orderByDesc('mpl_id')
                    ->first();

                $nextId = $lastPositionLevel
                    ? ((int) $lastPositionLevel->mpl_id + 1)
                    : 1;

                $reference = sprintf(
                    'MPL%s-%05d',
                    now()->format('Y'),
                    $nextId
                );

                return PositionLevel::create([
                    'mpl_reference' => $reference,
                    'mpl_name' => trim($validated['mpl_name']),
                    'mpl_status' => 'Active',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Position level created successfully.',
                'data' => $positionLevel,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Failed to create position level.', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create position level.',
            ], 500);
        }
    }

    /**
     * Display a specific position level.
     */
    public function show(PositionLevel $positionLevel): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Position level retrieved successfully.',
            'data' => $positionLevel,
        ]);
    }

    /**
     * Update a position level.
     */
    public function update(
        Request $request,
        PositionLevel $positionLevel
    ): JsonResponse {
        try {
            $validated = $request->validate([
                'mpl_reference' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:50',
                    Rule::unique(
                        (new PositionLevel())->getTable(),
                        'mpl_reference'
                    )->ignore(
                        $positionLevel->mpl_id,
                        'mpl_id'
                    ),
                ],
                'mpl_name' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255',
                ],
                'mpl_status' => [
                    'sometimes',
                    'required',
                    Rule::in(['Active', 'Inactive', 'Deleted']),
                ],
            ]);

            if (isset($validated['mpl_name'])) {
                $validated['mpl_name'] = trim($validated['mpl_name']);
            }

            $positionLevel->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Position level updated successfully.',
                'data' => $positionLevel->fresh(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Failed to update position level.', [
                'position_level_id' => $positionLevel->mpl_id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update position level.',
            ], 500);
        }
    }

    /**
     * Soft-delete a position level by changing its status.
     */
    public function destroy(PositionLevel $positionLevel): JsonResponse
    {
        try {
            if ($positionLevel->mpl_status === 'Deleted') {
                return response()->json([
                    'success' => false,
                    'message' => 'This position level has already been deleted.',
                ], 409);
            }

            $positionLevel->update([
                'mpl_status' => 'Deleted',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Position level deleted successfully.',
                'data' => $positionLevel->fresh(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to delete position level.', [
                'position_level_id' => $positionLevel->mpl_id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete position level.',
            ], 500);
        }
    }
}
