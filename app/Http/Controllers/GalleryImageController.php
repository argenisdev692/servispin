<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Throwable;
use App\Services\TransactionService;

class GalleryImageController extends BaseCrudController
{
    public function __construct(TransactionService $transactionService)
    {
        parent::__construct($transactionService);
        $this->modelClass = GalleryImage::class;
        $this->entityName = 'GalleryImage';
        $this->viewPrefix = 'admin.gallery-images';
        $this->routePrefix = 'gallery-images';
    }

    protected function getValidationRules($id = null)
    {
        $fileRule = [
            'file',
            'max:51200',
            function ($attribute, $value, $fail) {
                $ext = strtolower($value->getClientOriginalExtension());
                $allowed = ['jpeg', 'jpg', 'png', 'webp', 'mp4', 'mov', 'avi'];
                if (!in_array($ext, $allowed)) {
                    $fail('Only JPEG, PNG, JPG, WEBP images and MP4, MOV, AVI videos are allowed.');
                }
            },
        ];

        $rules = [
            'files' => 'required|array',
            'files.*' => $fileRule,
            'sort_order' => 'nullable|integer|min:0',
        ];

        if ($id) {
            $rules['files'] = 'nullable|array';
            $rules['files.*'] = $fileRule;
        }

        return $rules;
    }

    protected function getValidationMessages()
    {
        return [
            'files.required' => 'Please select at least one file to upload.',
            'files.*.max' => 'Each file must not exceed 50MB.',
        ];
    }

    private function processAndStoreFile($file)
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());
            $imageExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'];
            $isImage = in_array($extension, $imageExts);
            $type = $isImage ? 'image' : 'video';

            $filename = date('YmdHis') . '_' . Str::random(10) . '.' . $extension;
            $relativePath = 'gallery/' . $filename;
            $fullPath = storage_path('app/public/' . $relativePath);
            $dir = dirname($fullPath);

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            if ($isImage && extension_loaded('fileinfo')) {
                $image = Image::make($file->getRealPath());

                $maxWidth = 1200;
                $maxHeight = 1200;

                if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
                    $image->resize($maxWidth, $maxHeight, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }

                $encoded = $image->encode('jpg', 85);
                file_put_contents($fullPath, $encoded);
                return ['path' => $relativePath, 'type' => 'image'];
            }

            $contents = file_get_contents($file->getRealPath());
            file_put_contents($fullPath, $contents);
            return ['path' => $relativePath, 'type' => $type];
        } catch (\Exception $e) {
            Log::error('Error processing gallery file', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function deleteImageFile($relativePath)
    {
        if ($relativePath) {
            $fullPath = storage_path('app/public/' . $relativePath);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    public function serveFile($path)
    {
        $fullPath = storage_path('app/public/' . $path);
        if (!file_exists($fullPath)) {
            abort(404);
        }

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
        ];
        $mime = $mimes[$ext] ?? 'application/octet-stream';

        return response()->file($fullPath, ['Content-Type' => $mime]);
    }

    public function index(Request $request)
    {
        Log::debug('GalleryImageController@index method entered.');
        try {
            if ($request->ajax() || $request->wantsJson()) {
                $query = GalleryImage::query();


                $sortField = $request->input('sort_field', 'sort_order');
                $sortDirection = $request->input('sort_direction', 'asc');
                $query->orderBy($sortField, $sortDirection);

                $perPage = $request->input('per_page', 10);
                $images = $query->paginate($perPage);

                return response()->json($images);
            }

            return view('admin.gallery-images.index');
        } catch (Throwable $e) {
            Log::error('Error fetching gallery images', ['error' => $e->getMessage()]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error fetching gallery images: ' . $e->getMessage(),
                ], 500);
            }

            return view('admin.gallery-images.index')->with('error', 'Error loading gallery images.');
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->getValidationRules(), $this->getValidationMessages());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $files = $request->file('files');
            $startingSortOrder = $request->input('sort_order', 0);
            $createdImages = [];

            $galleryImages = $this->transactionService->run(
                function () use ($files, $startingSortOrder, &$createdImages) {
                    $images = [];
                    foreach ($files as $index => $file) {
                        $fileResult = $this->processAndStoreFile($file);

                        if (!$fileResult) {
                            throw new \Exception('Error processing file: ' . $file->getClientOriginalName());
                        }

                        $image = GalleryImage::create([
                            'uuid' => (string) Str::uuid(),
                            'type' => $fileResult['type'],
                            'file_path' => $fileResult['path'],
                            'sort_order' => $startingSortOrder + $index,
                        ]);

                        $images[] = $image;
                        $createdImages[] = $image;
                    }
                    return $images;
                },
                function ($created) {
                    Log::info('Gallery images created', ['count' => count($created)]);
                },
                function (Throwable $e) {
                    Log::error('Error creating gallery images', ['error' => $e->getMessage()]);
                }
            );

            return response()->json([
                'success' => true,
                'message' => count($galleryImages) . ' gallery image(s) uploaded successfully!',
                'galleryImages' => $galleryImages
            ]);
        } catch (Throwable $e) {
            Log::error('Error creating gallery images', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error creating gallery images: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($uuid)
    {
        try {
            if (!$uuid || $uuid === 'undefined' || !Str::isUuid($uuid)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid gallery image identifier.'
                ], 400);
            }

            $galleryImage = GalleryImage::where('uuid', $uuid)->firstOrFail();

            return response()->json([
                'success' => true,
                'galleryImage' => $galleryImage
            ]);
        } catch (Throwable $e) {
            Log::error('Error finding gallery image', ['uuid' => $uuid, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gallery image not found.'
            ], 404);
        }
    }

    public function update(Request $request, $uuid)
    {
        try {
            if (!$uuid || $uuid === 'undefined' || !Str::isUuid($uuid)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid gallery image identifier.'
                ], 400);
            }

            $galleryImage = GalleryImage::where('uuid', $uuid)->firstOrFail();

            $validator = Validator::make($request->all(), $this->getValidationRules($galleryImage->id), $this->getValidationMessages());

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $newFileResult = null;
            if ($request->hasFile('files') && count($request->file('files')) > 0) {
                $files = $request->file('files');
                $newFileResult = $this->processAndStoreFile($files[0]);
                if (!$newFileResult) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error processing file. Please try again.'
                    ], 500);
                }
            }

            $galleryImage = $this->transactionService->run(
                function () use ($request, $uuid, $newFileResult, $galleryImage) {
                    $entity = GalleryImage::where('uuid', $uuid)->firstOrFail();

                    $data = [
                        'sort_order' => $request->input('sort_order', 0),
                    ];

                    if ($newFileResult) {
                        $oldPath = $entity->file_path;
                        $data['file_path'] = $newFileResult['path'];
                        $data['type'] = $newFileResult['type'];
                        $entity->update($data);
                        $this->deleteImageFile($oldPath);
                    } else {
                        $entity->update($data);
                    }

                    Log::info('Gallery image updated', ['uuid' => $uuid]);
                    return $entity->fresh();
                },
                function ($updated) {},
                function (Throwable $e) use ($uuid, $newFileResult) {
                    if ($newFileResult) {
                        $this->deleteImageFile($newFileResult['path']);
                    }
                    Log::error('Error updating gallery image', ['uuid' => $uuid, 'error' => $e->getMessage()]);
                }
            );

            return response()->json([
                'success' => true,
                'message' => 'Gallery image updated successfully!',
                'galleryImage' => $galleryImage
            ]);
        } catch (Throwable $e) {
            Log::error('Error updating gallery image', ['uuid' => $uuid, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error updating gallery image: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($uuid)
    {
        try {
            if (!$uuid || $uuid === 'undefined' || !Str::isUuid($uuid)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid gallery image identifier.'
                ], 400);
            }

            $this->transactionService->run(
                function () use ($uuid) {
                    $entity = GalleryImage::where('uuid', $uuid)->firstOrFail();
                    $this->deleteImageFile($entity->file_path);
                    $entity->delete();
                    Log::info('Gallery image deleted', ['uuid' => $uuid]);
                },
                function ($deletedTitle) {},
                function (Throwable $e) use ($uuid) {
                    Log::error('Error deleting gallery image', ['uuid' => $uuid, 'error' => $e->getMessage()]);
                }
            );

            return response()->json([
                'success' => true,
                'message' => 'Gallery image deleted permanently!'
            ]);
        } catch (Throwable $e) {
            Log::error('Error deleting gallery image', ['uuid' => $uuid, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error deleting gallery image: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reorder(Request $request)
    {
        try {
            $items = $request->input('items', []);
            if (!is_array($items) || empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items provided for reordering.'
                ], 422);
            }

            foreach ($items as $index => $uuid) {
                GalleryImage::where('uuid', $uuid)->update(['sort_order' => $index]);
            }

            Log::info('Gallery images reordered', ['count' => count($items)]);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully!'
            ]);
        } catch (Throwable $e) {
            Log::error('Error reordering gallery images', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error updating order: ' . $e->getMessage()
            ], 500);
        }
    }

}
