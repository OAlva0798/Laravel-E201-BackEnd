<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class SystemSettingController extends Controller
{
    /**
     * GET ALL SETTINGS
     *
     * GET /api/system-settings
     */
    public function index()
    {
        try {
            $settings = SystemSetting::query()->orderBy('ss_id')->get();

            return response()->json([
                'success' => true,
                'data' => $settings,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve system settings.',
                'data' => [],
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET SYSTEM INFORMATION
     *
     * GET /api/system-settings/SystemInfo
     */
    public function getSystemInfo()
    {
        try {
            $settings = SystemSetting::query()
                ->select(['ss_id', 'ss_field', 'ss_value', 'ss_type'])
                ->orderBy('ss_id')
                ->get();

            $systemInfo = [];

            foreach ($settings as $setting) {
                $field = trim((string) $setting->ss_field);

                if ($field === '') {
                    continue;
                }

                $value = $setting->ss_value;

                if (
                    in_array(strtolower((string) $setting->ss_type), ['image', 'video'], true)
                    && !empty($value)
                ) {
                    $value = $this->makePublicFileUrl($value);
                }

                $systemInfo[$field] = $value;
            }

            return response()->json([
                'success' => true,
                'message' => 'System information retrieved successfully.',
                'data' => $systemInfo,
                'settings' => $settings,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve system information.',
                'data' => [],
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * STORE / UPDATE MULTIPLE SETTINGS
     *
     * POST /api/system-settings
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'ss_id' => ['nullable', 'array'],
            'ss_id.*' => ['nullable', 'integer'],

            'ss_field' => ['required', 'array', 'min:1'],
            'ss_field.*' => ['required', 'string', 'max:255'],

            'ss_type' => ['required', 'array', 'min:1'],
            'ss_type.*' => ['required', 'string', 'max:50'],

            'ss_value' => ['nullable', 'array'],

            'ss_remove' => ['nullable', 'array'],
            'ss_remove.*' => ['nullable'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid system settings data.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $fields = $request->input('ss_field', []);
        $types = $request->input('ss_type', []);
        $ids = $request->input('ss_id', []);
        $removes = $request->input('ss_remove', []);

        $count = count($fields);

        if (count($types) !== $count) {
            return response()->json([
                'success' => false,
                'message' => 'The number of setting fields and types does not match.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            for ($index = 0; $index < $count; $index++) {
                $field = trim((string) ($fields[$index] ?? ''));
                $type = strtolower(trim((string) ($types[$index] ?? 'text')));
                $id = $ids[$index] ?? null;

                $remove = isset($removes[$index]) && in_array($removes[$index], ['1', 1, true, 'true'], true);

                if ($field === '') {
                    continue;
                }

                // Allowed setting types
                $allowedTypes = [
                    'text',
                    'password',
                    'email',
                    'number',
                    'url',
                    'tel',
                    'date',
                    'time',
                    'datetime-local',
                    'month',
                    'week',
                    'select',
                    'textarea',
                    'quill',
                    'checkbox',
                    'switch',
                    'color',
                    'range',
                    'image',
                    'video',
                ];

                if (!in_array($type, $allowedTypes, true)) {
                    throw new InvalidArgumentException("Unsupported system setting type: {$type}");
                }

                // Find existing setting
                $setting = null;

                if (!empty($id)) {
                    $setting = SystemSetting::find($id);
                }

                if (!$setting) {
                    $setting = SystemSetting::where('ss_field', $field)->first();
                }

                // Create setting if needed
                if (!$setting) {
                    $setting = new SystemSetting();
                    $setting->ss_field = $field;
                }

                $oldValue = $setting->ss_value;

                // Remove current file
                if ($remove && in_array($type, ['image', 'video'], true)) {
                    $this->deleteSettingFile($oldValue);

                    $setting->ss_value = '';
                    $setting->ss_field = $field;
                    $setting->ss_type = $type;
                    $setting->save();

                    continue;
                }

                // Check uploaded file
                $file = $request->file("ss_value.{$index}");

                if ($file instanceof UploadedFile) {
                    if (!$file->isValid()) {
                        throw new RuntimeException("The uploaded file for {$field} is invalid.");
                    }

                    $this->validateUploadedFile($file, $type);

                    $newPath = $this->storeSettingFile($file, $type);
                    $setting->ss_value = $newPath;

                    // Delete previous file
                    if ($oldValue && $oldValue !== $newPath) {
                        $this->deleteSettingFile($oldValue);
                    }
                } else {
                    // No uploaded file — existing image/video remains unchanged.
                    if (in_array($type, ['image', 'video'], true)) {
                        // Keep current file.
                    } else {
                        $value = $request->input("ss_value.{$index}");
                        $setting->ss_value = $value ?? '';
                    }
                }

                $setting->ss_field = $field;
                $setting->ss_type = $type;
                $setting->save();
            }

            DB::commit();

            $settings = SystemSetting::query()->orderBy('ss_id')->get();

            return response()->json([
                'success' => true,
                'message' => 'System settings saved successfully.',
                'data' => $settings,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to save system settings.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * SHOW ONE SETTING
     *
     * GET /api/system-settings/{id}
     */
    public function show($id)
    {
        try {
            $setting = SystemSetting::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $setting,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'System setting not found.',
            ], 404);
        }
    }

    /**
     * UPDATE ONE SETTING
     *
     * PUT/PATCH /api/system-settings/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $setting = SystemSetting::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'ss_field' => ['sometimes', 'required', 'string', 'max:255'],
                'ss_type' => ['sometimes', 'required', 'string', 'max:50'],
                'ss_value' => ['sometimes', 'nullable'],
                'ss_remove' => ['nullable'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid system setting data.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $type = strtolower(trim($request->input('ss_type', $setting->ss_type ?? 'text')));
            $oldValue = $setting->ss_value;
            $remove = $request->boolean('ss_remove');

            if ($remove && in_array($type, ['image', 'video'], true)) {
                // Remove
                $this->deleteSettingFile($oldValue);
                $setting->ss_value = '';
            } elseif ($request->hasFile('ss_value')) {
                // New file
                $file = $request->file('ss_value');

                if (!$file->isValid()) {
                    throw new RuntimeException('Uploaded file is invalid.');
                }

                $this->validateUploadedFile($file, $type);

                $newPath = $this->storeSettingFile($file, $type);
                $setting->ss_value = $newPath;

                $this->deleteSettingFile($oldValue);
            } elseif ($request->has('ss_value')) {
                // Normal value
                $setting->ss_value = $request->input('ss_value');
            }

            // Field
            if ($request->has('ss_field')) {
                $setting->ss_field = trim($request->input('ss_field'));
            }

            $setting->ss_type = $type;
            $setting->save();

            return response()->json([
                'success' => true,
                'message' => 'System setting updated successfully.',
                'data' => $setting,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update system setting.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * DELETE ONE SETTING
     *
     * DELETE /api/system-settings/{id}
     */
    public function destroy($id)
    {
        try {
            $setting = SystemSetting::findOrFail($id);

            $this->deleteSettingFile($setting->ss_value);

            $setting->delete();

            return response()->json([
                'success' => true,
                'message' => 'System setting deleted successfully.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to delete system setting.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * MAKE PUBLIC FILE URL
     */
    private function makePublicFileUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = trim($path);

        if ($path === '') {
            return null;
        }

        if (Str::startsWith(strtolower($path), ['http://', 'https://', 'data:'])) {
            return $path;
        }

        return url(ltrim($path, '/'));
    }

    /**
     * VALIDATE FILE
     */
    private function validateUploadedFile($file, string $type): void
    {
        $type = strtolower(trim($type));

        if ($type === 'image') {
            Validator::make(['file' => $file], [
                'file' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240'],
            ])->validate();

            return;
        }

        if ($type === 'video') {
            Validator::make(['file' => $file], [
                'file' => ['required', 'file', 'mimes:mp4,webm,ogg,mov', 'max:51200'],
            ])->validate();

            return;
        }

        throw new InvalidArgumentException("Unsupported file setting type: {$type}");
    }

    /**
     * STORE FILE
     */
    private function storeSettingFile($file, string $type): string
    {
        $directory = public_path('files/setting_image');

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        // Remove symbols and special characters
        $cleanName = preg_replace('/[^A-Za-z0-9_\-\s]/', '', $originalName);

        // Convert spaces to underscores
        $cleanName = preg_replace('/\s+/', '_', trim($cleanName));

        // Fallback name if original name contained only symbols
        if ($cleanName === '') {
            $cleanName = $type;
        }

        // Output format: originalfilename_YYYYMMDDHHMMSS.extension
        $filename = $cleanName . '_' . now()->format('YmdHis') . '.' . $extension;

        $file->move($directory, $filename);

        return 'files/setting_image/' . $filename;
    }

    /**
     * DELETE FILE
     */
    private function deleteSettingFile(?string $path): void
    {
        if (!$path) {
            return;
        }

        $path = ltrim(trim($path), '/');

        // Only delete our own files
        if (!Str::startsWith($path, 'files/setting_image/')) {
            return;
        }

        $fullPath = public_path($path);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}