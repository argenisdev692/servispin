<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\TransactionService;
use Throwable;

abstract class BaseCrudController extends Controller
{
    protected $modelClass;
    protected $entityName;
    protected $viewPrefix;
    protected $routePrefix;
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display a listing of entities.
     * Handles both view request and AJAX data request.
     */
    public function index(Request $request)
    {
        Log::debug($this->entityName.'Controller@index method entered.');
        try {
            if ($request->ajax() || $request->wantsJson()) {
                Log::debug('AJAX request detected in '.$this->entityName.'Controller@index.', $request->all());
                $query = $this->modelClass::query();
                
                // Apply search filter if provided
                if ($request->has('search') && !empty($request->search)) {
                    $searchTerm = '%' . $request->search . '%';
                    $query->where($this->getSearchField(), 'like', $searchTerm);
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
                    Log::debug('Including soft-deleted entities.');
                }
                
                // Paginate results
                $perPage = $request->input('per_page', 10);
                $entities = $query->paginate($perPage);
                
                Log::debug('Entities fetched successfully for AJAX request.', [
                    'count' => $entities->count(),
                    'total' => $entities->total(),
                    'currentPage' => $entities->currentPage(),
                    'perPage' => $entities->perPage(),
                ]);
                
                return response()->json($entities);
            }
            
            Log::debug('Non-AJAX request detected, returning view.');
            // Return the view for non-AJAX requests
            return view($this->viewPrefix . '.index');
        } catch (Throwable $e) {
            Log::error('Error fetching entities in '.$this->entityName.'Controller@index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error fetching entities: ' . $e->getMessage(),
                    'error_details' => $e->getTraceAsString()
                ], 500);
            }
            
            return view($this->viewPrefix . '.index')->with('error', 'Error loading entities. Please try again.');
        }
    }

    /**
     * Store a newly created entity.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->getValidationRules(), $this->getValidationMessages());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $entity = $this->transactionService->run(
                // Database operations
                function () use ($request) {
                    $data = $this->prepareStoreData($request);
                    $entity = $this->modelClass::create($data);
                    
                    Log::info($this->entityName.' created successfully', ['uuid' => $entity->uuid]);
                    return $entity;
                },
                // On commit
                function ($createdEntity) {
                    // Any additional actions after successful commit
                    $this->afterStore($createdEntity);
                },
                // On error
                function (Throwable $e) {
                    Log::error('Error creating '.$this->entityName, ['error' => $e->getMessage()]);
                }
            );
            
            return response()->json([
                'success' => true,
                'message' => $this->entityName.' created successfully!',
                'entity' => $entity
            ]);
        } catch (Throwable $e) {
            Log::error('Error creating '.$this->entityName, ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating '.$this->entityName.': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch a specific entity for editing.
     */
    public function edit($uuid)
    {
        try {
            $entity = $this->modelClass::withTrashed()->where('uuid', $uuid)->firstOrFail();
            
            return response()->json([
                'success' => true,
                $this->getEntityVarName() => $entity
            ]);
        } catch (Throwable $e) {
            Log::error('Error finding '.$this->entityName.' for edit', ['uuid' => $uuid, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => $this->entityName.' not found.'
            ], 404);
        }
    }

    /**
     * Update the specified entity.
     */
    public function update(Request $request, $uuid)
    {
        try {
            $entity = $this->modelClass::withTrashed()->where('uuid', $uuid)->firstOrFail();
            
            $validator = Validator::make($request->all(), $this->getValidationRules($entity->id), $this->getValidationMessages());

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $entity = $this->transactionService->run(
                // Database operations
                function () use ($request, $uuid) {
                    $entity = $this->modelClass::withTrashed()->where('uuid', $uuid)->firstOrFail();
                    
                    $data = $this->prepareUpdateData($request);
                    $entity->update($data);
                    
                    Log::info($this->entityName.' updated successfully', ['uuid' => $uuid]);
                    return $entity;
                },
                // On commit
                function ($updatedEntity) {
                    // Any additional actions after successful commit
                    $this->afterUpdate($updatedEntity);
                },
                // On error
                function (Throwable $e) use ($uuid) {
                    Log::error('Error updating '.$this->entityName, ['uuid' => $uuid, 'error' => $e->getMessage()]);
                }
            );
            
            return response()->json([
                'success' => true,
                'message' => $this->entityName.' updated successfully!',
                $this->getEntityVarName() => $entity
            ]);
        } catch (Throwable $e) {
            Log::error('Error updating '.$this->entityName, ['uuid' => $uuid, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating '.$this->entityName.': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete the specified entity.
     */
    public function destroy($uuid)
    {
        try {
            $entityName = $this->transactionService->run(
                // Database operations
                function () use ($uuid) {
                    $entity = $this->modelClass::where('uuid', $uuid)->firstOrFail();
                    $entityName = $this->getEntityDisplayName($entity);
                    
                    $entity->delete();
                    
                    Log::info($this->entityName.' deleted successfully', ['uuid' => $uuid]);
                    return $entityName;
                },
                // On commit
                function ($deletedEntityName) {
                    // Any additional actions after successful commit
                    $this->afterDestroy($deletedEntityName);
                },
                // On error
                function (Throwable $e) use ($uuid) {
                    Log::error('Error deleting '.$this->entityName, ['uuid' => $uuid, 'error' => $e->getMessage()]);
                }
            );
            
            return response()->json([
                'success' => true,
                'message' => $this->entityName.' "' . $entityName . '" moved to trash successfully!'
            ]);
        } catch (Throwable $e) {
            Log::error('Error deleting '.$this->entityName, ['uuid' => $uuid, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting '.$this->entityName.': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted entity.
     */
    public function restore($uuid)
    {
        try {
            $entityName = $this->transactionService->run(
                // Database operations
                function () use ($uuid) {
                    $entity = $this->modelClass::onlyTrashed()->where('uuid', $uuid)->firstOrFail();
                    $entityName = $this->getEntityDisplayName($entity);
                    
                    $entity->restore();
                    
                    Log::info($this->entityName.' restored successfully', ['uuid' => $uuid]);
                    return $entityName;
                },
                // On commit
                function ($restoredEntityName) {
                    // Any additional actions after successful commit
                    $this->afterRestore($restoredEntityName);
                },
                // On error
                function (Throwable $e) use ($uuid) {
                    Log::error('Error restoring '.$this->entityName, ['uuid' => $uuid, 'error' => $e->getMessage()]);
                }
            );
            
            return response()->json([
                'success' => true,
                'message' => $this->entityName.' "' . $entityName . '" restored successfully!'
            ]);
        } catch (Throwable $e) {
            Log::error('Error restoring '.$this->entityName, ['uuid' => $uuid, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error restoring '.$this->entityName.': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if an entity name already exists.
     * Used for real-time validation.
     */
    public function checkNameExists(Request $request)
    {
        $name = $request->input('name');
        $excludeUuid = $request->input('exclude_uuid');
        
        $query = $this->modelClass::where($this->getNameField(), $name);
        
        // If we're editing, exclude the current entity
        if ($excludeUuid) {
            $query->where('uuid', '!=', $excludeUuid);
        }
        
        $exists = $query->withTrashed()->exists();
        
        return response()->json([
            'exists' => $exists
        ]);
    }

    // Abstract methods that must be implemented by child classes
    abstract protected function getValidationRules($id = null);
    abstract protected function getValidationMessages();
    
    // Methods with default implementations that can be overridden by child classes
    protected function getSearchField()
    {
        return 'name';
    }
    
    protected function getNameField()
    {
        return 'name';
    }
    
    protected function getEntityVarName()
    {
        return strtolower($this->entityName);
    }
    
    protected function getEntityDisplayName($entity)
    {
        return $entity->{$this->getNameField()};
    }
    
    protected function prepareStoreData(Request $request)
    {
        return $request->all();
    }
    
    protected function prepareUpdateData(Request $request)
    {
        return $request->all();
    }
    
    protected function afterStore($entity)
    {
        // Default implementation does nothing
    }
    
    protected function afterUpdate($entity)
    {
        // Default implementation does nothing
    }
    
    protected function afterDestroy($entityName)
    {
        // Default implementation does nothing
    }
    
    protected function afterRestore($entityName)
    {
        // Default implementation does nothing
    }
} 