<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class EmployeeDocumentController extends Controller
{
    private const DIRECTORY = 'employees';

    private const TYPES = [
        'Bio-Data',
        'Appointment Paper',
        'Qualifications',
        'Educational Qualifications',
        'Training & Seminars',
        'Evaluations',
        'Seagoing Experience',
        'Teaching Experience',
        'Miscellaneous',
    ];

    /**
     * List all E-201 documents for an employee.
     */
    public function index(string $idNumber): JsonResponse
    {
        $documents = EmployeeDocument::query()
            ->where('eed_id_number', $idNumber)
            ->orderBy('eed_type')
            ->orderByDesc('eed_created_at')
            ->get()
            ->map(fn (EmployeeDocument $document) => $this->formatDocument($document))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $documents,
        ]);
    }

    /**
     * Upload multiple E-201 documents.
     */
    public function store(Request $request, string $idNumber): JsonResponse
    {
        $validated = $request->validate([
            'eed_type' => [
                'required',
                'string',
                'in:' . implode(',', self::TYPES),
            ],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,jpg,jpeg,png',
            ],
        ]);

        $created = [];
        $storedPaths = [];

        try {
            DB::transaction(function () use (
                $request,
                $idNumber,
                $validated,
                &$created,
                &$storedPaths
            ) {
                foreach ($request->file('files') as $file) {
                    $originalName = basename($file->getClientOriginalName());

                    $directory = self::DIRECTORY
                        . '/'
                        . $idNumber
                        . '/e-201';

                    $targetStoragePath = $directory . '/' . $originalName;

                    $existsInDatabase = EmployeeDocument::query()
                        ->where('eed_id_number', $idNumber)
                        ->where('eed_file', $originalName)
                        ->exists();

                    $existsInStorage = Storage::disk('public')
                        ->exists($targetStoragePath);

                    if ($existsInDatabase || $existsInStorage) {
                        throw ValidationException::withMessages([
                            'files' => [
                                "The file '{$originalName}' already exists for this employee.",
                            ],
                        ]);
                    }

                    $path = $file->storeAs(
                        $directory,
                        $originalName,
                        'public'
                    );

                    if (!$path) {
                        throw new \RuntimeException(
                            'Unable to store uploaded file.'
                        );
                    }

                    $storedPaths[] = $path;

                    // Save only the filename in the database.
                    $created[] = EmployeeDocument::create([
                        'eed_id_number' => $idNumber,
                        'eed_type' => $validated['eed_type'],
                        'eed_file' => $originalName,
                        'eed_file_ext' => strtolower(
                            $file->getClientOriginalExtension()
                        ),
                    ]);
                }
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }

        return response()->json([
            'success' => true,
            'message' => 'E-201 documents uploaded successfully.',
            'data' => collect($created)
                ->map(fn (EmployeeDocument $document) => $this->formatDocument($document))
                ->values(),
        ], 201);
    }

    /**
     * Get a single document's information.
     */
    public function show(string $idNumber, int $documentId): JsonResponse
    {
        $document = $this->findDocument($idNumber, $documentId);

        return response()->json([
            'success' => true,
            'data' => $this->formatDocument($document),
        ]);
    }


    public function download(string $idNumber, int $documentId): StreamedResponse
    {
        $document = $this->findDocument($idNumber, $documentId);
        $fullPath = $this->getStoragePath($document);

        abort_unless(
            $document->eed_file && Storage::disk('public')->exists($fullPath),
            404,
            'Document file not found.'
        );

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->download($fullPath, $document->eed_file);
    }

    public function view(string $idNumber, int $documentId)
    {
        $document = $this->findDocument($idNumber, $documentId);
        $fullPath = $this->getStoragePath($document);

        abort_unless(
            $document->eed_file && Storage::disk('public')->exists($fullPath),
            404,
            'Document file not found.'
        );

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $mimeType = $disk->mimeType($fullPath);

        abort_unless(
            in_array($mimeType, [
                'application/pdf',
                'image/jpeg',
                'image/png',
            ], true),
            415,
            'This file type cannot be previewed.'
        );

        return response()->file(
            $disk->path($fullPath),
            [
                'Content-Type' => $mimeType,
                'X-Content-Type-Options' => 'nosniff',
                'Content-Disposition' => 'inline',
            ]
        );
    }

    /**
     * Update a document's category and/or replace its file.
     */
    public function update(
        Request $request,
        string $idNumber,
        int $documentId
    ): JsonResponse {
        $document = $this->findDocument($idNumber, $documentId);

        $validated = $request->validate([
            'eed_type' => [
                'sometimes',
                'required',
                'string',
                'in:' . implode(',', self::TYPES),
            ],
            'file' => [
                'sometimes',
                'required',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,jpg,jpeg,png',
            ],
        ]);

        $oldFileName = $document->eed_file;
        $oldStoragePath = $this->getStoragePath($document);
        $newStoragePath = null;
        $backupPath = null;
        $uploadedFile = $request->file('file');

        if ($uploadedFile) {
            $originalName = basename(
                $uploadedFile->getClientOriginalName()
            );

            $directory = self::DIRECTORY
                . '/'
                . $idNumber
                . '/e-201';

            $targetStoragePath = $directory . '/' . $originalName;

            $existsInDatabase = EmployeeDocument::query()
                ->where('eed_id_number', $idNumber)
                ->where('eed_id', '!=', $documentId)
                ->where('eed_file', $originalName)
                ->exists();

            $existsInStorage = Storage::disk('public')
                ->exists($targetStoragePath);

            if (
                $existsInDatabase
                || (
                    $existsInStorage
                    && $targetStoragePath !== $oldStoragePath
                )
            ) {
                throw ValidationException::withMessages([
                    'file' => [
                        "The file '{$originalName}' already exists for this employee.",
                    ],
                ]);
            }

            // Back up the existing file when replacing it with the same name.
            if ($targetStoragePath === $oldStoragePath) {
                $backupPath = $directory
                    . '/temp_backup_'
                    . uniqid('', true)
                    . '_'
                    . $oldFileName;

                $moved = Storage::disk('public')->move(
                    $oldStoragePath,
                    $backupPath
                );

                if (!$moved) {
                    throw new \RuntimeException(
                        'Unable to back up the existing document.'
                    );
                }
            }

            $newStoragePath = $uploadedFile->storeAs(
                $directory,
                $originalName,
                'public'
            );

            if (!$newStoragePath) {
                if ($backupPath) {
                    Storage::disk('public')->move(
                        $backupPath,
                        $oldStoragePath
                    );
                }

                throw new \RuntimeException(
                    'Unable to store replacement file.'
                );
            }

            // Save only the filename.
            $document->eed_file = $originalName;
            $document->eed_file_ext = strtolower(
                $uploadedFile->getClientOriginalExtension()
            );
        }

        if (isset($validated['eed_type'])) {
            $document->eed_type = $validated['eed_type'];
        }

        try {
            DB::transaction(function () use ($document) {
                $document->save();
            });
        } catch (Throwable $exception) {
            if ($newStoragePath) {
                Storage::disk('public')->delete($newStoragePath);
            }

            if ($backupPath) {
                Storage::disk('public')->move(
                    $backupPath,
                    $oldStoragePath
                );
            }

            throw $exception;
        }

        // Clean up old or temporary files after a successful update.
        if ($backupPath) {
            Storage::disk('public')->delete($backupPath);
        } elseif (
            $newStoragePath
            && $oldStoragePath
            && $newStoragePath !== $oldStoragePath
        ) {
            Storage::disk('public')->delete($oldStoragePath);
        }

        return response()->json([
            'success' => true,
            'message' => 'Document updated successfully.',
            'data' => $this->formatDocument($document),
        ]);
    }

    /**
     * Delete a document and its stored file.
     */
    public function destroy(
        string $idNumber,
        int $documentId
    ): JsonResponse {
        $document = $this->findDocument($idNumber, $documentId);
        $storagePath = $this->getStoragePath($document);

        DB::transaction(function () use ($document) {
            $document->delete();
        });

        if (
            $storagePath
            && Storage::disk('public')->exists($storagePath)
        ) {
            Storage::disk('public')->delete($storagePath);
        }

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully.',
        ]);
    }

    /**
     * Resolve the actual storage path from the filename stored in the database.
     */
    private function getStoragePath(EmployeeDocument $document): string
    {
        if (!$document->eed_file || !$document->eed_id_number) {
            return '';
        }

        // Support older records that already contain a relative storage path.
        if (str_contains($document->eed_file, '/')) {
            return $document->eed_file;
        }

        return self::DIRECTORY
            . '/'
            . $document->eed_id_number
            . '/e-201/'
            . $document->eed_file;
    }

    /**
     * Find a document belonging to the specified employee.
     */
    private function findDocument(
        string $idNumber,
        int $documentId
    ): EmployeeDocument {
        return EmployeeDocument::query()
            ->where('eed_id_number', $idNumber)
            ->where('eed_id', $documentId)
            ->firstOrFail();
    }

    /**
     * Format a document and report whether its physical file exists.
     */
    private function formatDocument(EmployeeDocument $document): array
    {
        $disk = Storage::disk('public');
        $storagePath = $this->getStoragePath($document);

        $hasFile = $storagePath !== ''
            && $disk->exists($storagePath);

        $size = 'Unknown';

        if ($hasFile) {
            $size = $this->formatBytes(
                $disk->size($storagePath)
            );
        }

        return [
            'eed_id' => $document->eed_id,
            'eed_id_number' => $document->eed_id_number,
            'eed_type' => $document->eed_type,
            'eed_file' => $document->eed_file,
            'eed_file_ext' => $document->eed_file_ext,
            'name' => $document->eed_file ?: 'Unnamed document',
            'size' => $size,

            // Used by EmployeesContent to count existing and missing files.
            'file_exists' => (bool) $hasFile,

            'created_at' => $document->eed_created_at,
            'updated_at' => $document->eed_updated_at,
        ];
    }

    /**
     * Format a byte count into a readable size.
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' Bytes';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $size = $bytes / 1024;
        $index = 0;

        while (
            $size >= 1024
            && $index < count($units) - 1
        ) {
            $size /= 1024;
            $index++;
        }

        return round($size, 2) . ' ' . $units[$index];
    }
}