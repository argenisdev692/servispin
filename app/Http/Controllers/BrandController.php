<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Services\TransactionService;

class BrandController extends BaseCrudController
{
    public function __construct(TransactionService $transactionService)
    {
        parent::__construct($transactionService);
        $this->modelClass = Brand::class;
        $this->entityName = 'Brand';
        $this->viewPrefix = 'admin.brands';
        $this->routePrefix = 'brands';
    }

    /**
     * Get validation rules for brand
     */
    protected function getValidationRules($id = null)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:brands,name'
        ];
        
        // If updating, exclude the current brand from unique check
        if ($id) {
            $rules['name'] .= ',' . $id;
        }
        
        return $rules;
    }
    
    /**
     * Get validation messages for brand
     */
    protected function getValidationMessages()
    {
        return [
            'name.required' => 'The brand name is required.',
            'name.unique' => 'This brand name is already taken.'
        ];
    }
    
    /**
     * Prepare data for storing a brand
     */
    protected function prepareStoreData(Request $request)
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => $request->name,
            // Add other fields as needed
        ];
    }
    
    /**
     * Prepare data for updating a brand
     */
    protected function prepareUpdateData(Request $request)
    {
        return [
            'name' => $request->name,
            // Add other fields as needed
        ];
    }

    /**
     * Display a listing of brands.
     * Handles both view request and AJAX data request.
     */
    public function index(Request $request)
    {
        Log::debug('BrandController@index method entered.');
        try {
            if ($request->ajax() || $request->wantsJson()) {
                Log::debug('AJAX request detected in BrandController@index.', $request->all());
                $query = Brand::query();
                
                // Apply search filter if provided
                if ($request->has('search') && !empty($request->search)) {
                    $searchTerm = '%' . $request->search . '%';
                    $query->where('name', 'like', $searchTerm);
                    Log::debug('Applying search filter.', ['term' => $request->search]);
                }
                
                // Apply sorting
                $sortField = $request->input('sort_field', 'created_at');
                $sortDirection = $request->input('sort_direction', 'desc');
                $query->orderBy($sortField, $sortDirection);
                Log::debug('Applying sorting.', ['field' => $sortField, 'direction' => $sortDirection]);
                
                // Show deleted items if requested
                if ($request->has('show_deleted') && $request->show_deleted === 'true') {
                    $query->withTrashed();
                    Log::debug('Including soft-deleted brands.');
                }
                
                // Paginate results
                $perPage = $request->input('per_page', 10);
                $brands = $query->paginate($perPage);
                
                // Log success and data before returning
                Log::debug('Brands fetched successfully for AJAX request.', [
                    'count' => $brands->count(),
                    'total' => $brands->total(),
                    'currentPage' => $brands->currentPage(),
                    'perPage' => $brands->perPage(),
                ]);
                
                return response()->json($brands);
            }
            
            Log::debug('Non-AJAX request detected, returning view.');
            // Return the view for non-AJAX requests
            return view('admin.brands.index');
        } catch (Throwable $e) {
            Log::error('Error fetching brands in BrandController@index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error fetching brands: ' . $e->getMessage(),
                    'error_details' => $e->getTraceAsString()
                ], 500);
            }
            
            return view('admin.brands.index')->with('error', 'Error loading brands. Please try again.');
        }
    }

    /**
     * Store a newly created brand.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:brands,name'
        ], [
            'name.required' => 'The brand name is required.',
            'name.unique' => 'This brand name is already taken.'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $brand = $this->transactionService->run(
                // Database operations
                function () use ($request) {
                    $brand = Brand::create([
                        'uuid' => (string) Str::uuid(),
                        'name' => $request->name,
                        // 'logo_path' => $request->logo_path // Implement file upload handling if needed
                    ]);
                    
                    Log::info('Brand created successfully', ['uuid' => $brand->uuid]);
                    return $brand;
                },
                // On commit
                function ($createdBrand) {
                    // Any additional actions after successful commit
                },
                // On error
                function (Throwable $e) {
                    Log::error('Error creating brand', ['error' => $e->getMessage()]);
                }
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Brand created successfully!',
                'brand' => $brand
            ]);
        } catch (Throwable $e) {
            Log::error('Error creating brand', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating brand: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch a specific brand for editing.
     */
    public function edit($uuid)
    {
        try {
            // Log the incoming UUID for debugging purposes
            Log::debug('Attempting to edit brand', ['uuid' => $uuid]);
            
            // Basic validation for UUID format
            if (!$uuid || $uuid === 'undefined' || !Str::isUuid($uuid)) {
                Log::error('Error editing brand', ['uuid' => $uuid, 'error' => 'Invalid UUID format']);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid brand identifier provided.'
                ], 400);
            }
            
            $brand = Brand::withTrashed()->where('uuid', $uuid)->firstOrFail();
            
            return response()->json([
                'success' => true,
                'brand' => $brand
            ]);
        } catch (Throwable $e) {
            Log::error('Error finding brand for edit', ['uuid' => $uuid, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Brand not found.'
            ], 404);
        }
    }

    /**
     * Update the specified brand.
     */
    public function update(Request $request, $uuid)
    {
        try {
            // Log the incoming UUID for debugging purposes
            Log::debug('Attempting to update brand', ['uuid' => $uuid]);
            
            // Basic validation for UUID format
            if (!$uuid || $uuid === 'undefined' || !Str::isUuid($uuid)) {
                Log::error('Error updating brand', ['uuid' => $uuid, 'error' => 'Invalid UUID format']);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid brand identifier provided.'
                ], 400);
            }
            
            $brand = Brand::withTrashed()->where('uuid', $uuid)->firstOrFail();
            
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:brands,name,' . $brand->id
            ], [
                'name.required' => 'The brand name is required.',
                'name.unique' => 'This brand name is already taken.'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $brand = $this->transactionService->run(
                // Database operations
                function () use ($request, $uuid) {
                    $brand = Brand::withTrashed()->where('uuid', $uuid)->firstOrFail();
                    
                    $brand->update([
                        'name' => $request->name,
                        // 'logo_path' => $request->logo_path // Implement file upload handling if needed
                    ]);
                    
                    Log::info('Brand updated successfully', ['uuid' => $uuid]);
                    return $brand;
                },
                // On commit
                function ($updatedBrand) {
                    // Any additional actions after successful commit
                },
                // On error
                function (Throwable $e) use ($uuid) {
                    Log::error('Error updating brand', ['uuid' => $uuid, 'error' => $e->getMessage()]);
                }
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Brand updated successfully!',
                'brand' => $brand
            ]);
        } catch (Throwable $e) {
            Log::error('Error updating brand', ['uuid' => $uuid, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating brand: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete the specified brand.
     */
    public function destroy($uuid)
    {
        try {
            // Log the incoming UUID for debugging purposes
            Log::debug('Attempting to delete brand', ['uuid' => $uuid]);
            
            // Basic validation for UUID format
            if (!$uuid || $uuid === 'undefined' || !Str::isUuid($uuid)) {
                Log::error('Error deleting brand', ['uuid' => $uuid, 'error' => 'Invalid UUID format']);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid brand identifier provided.'
                ], 400);
            }
            
            $brandName = $this->transactionService->run(
                // Database operations
                function () use ($uuid) {
                    $brand = Brand::where('uuid', $uuid)->firstOrFail();
                    $brandName = $brand->name;
                    
                    $brand->delete();
                    
                    Log::info('Brand deleted successfully', ['uuid' => $uuid]);
                    return $brandName;
                },
                // On commit
                function ($deletedBrandName) {
                    // Any additional actions after successful commit
                },
                // On error
                function (Throwable $e) use ($uuid) {
                    Log::error('Error deleting brand', ['uuid' => $uuid, 'error' => $e->getMessage()]);
                }
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Brand "' . $brandName . '" moved to trash successfully!'
            ]);
        } catch (Throwable $e) {
            Log::error('Error deleting brand', ['uuid' => $uuid, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting brand: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted brand.
     */
    public function restore($uuid)
    {
        try {
            // Log the incoming UUID for debugging purposes
            Log::debug('Attempting to restore brand', ['uuid' => $uuid]);
            
            // Basic validation for UUID format
            if (!$uuid || $uuid === 'undefined' || !Str::isUuid($uuid)) {
                Log::error('Error restoring brand', ['uuid' => $uuid, 'error' => 'Invalid UUID format']);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid brand identifier provided.'
                ], 400);
            }
            
            $brandName = $this->transactionService->run(
                // Database operations
                function () use ($uuid) {
                    $brand = Brand::onlyTrashed()->where('uuid', $uuid)->firstOrFail();
                    $brandName = $brand->name;
                    
                    $brand->restore();
                    
                    Log::info('Brand restored successfully', ['uuid' => $uuid]);
                    return $brandName;
                },
                // On commit
                function ($restoredBrandName) {
                    // Any additional actions after successful commit
                },
                // On error
                function (Throwable $e) use ($uuid) {
                    Log::error('Error restoring brand', ['uuid' => $uuid, 'error' => $e->getMessage()]);
                }
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Brand "' . $brandName . '" restored successfully!'
            ]);
        } catch (Throwable $e) {
            Log::error('Error restoring brand', ['uuid' => $uuid, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error restoring brand: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if a brand name already exists.
     * Used for real-time validation.
     */
    public function checkNameExists(Request $request)
    {
        $name = $request->input('name');
        $excludeUuid = $request->input('exclude_uuid');
        
        $query = Brand::where('name', $name);
        
        // If we're editing, exclude the current brand
        if ($excludeUuid) {
            $query->where('uuid', '!=', $excludeUuid);
        }
        
        $exists = $query->withTrashed()->exists();
        
        return response()->json([
            'exists' => $exists
        ]);
    }
} 