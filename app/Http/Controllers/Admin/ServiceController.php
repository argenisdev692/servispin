<?php

namespace App\Http\Controllers\Admin;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Services\TransactionService;
use App\Http\Controllers\BaseCrudController;

class ServiceController extends BaseCrudController
{
    public function __construct(TransactionService $transactionService)
    {
        parent::__construct($transactionService);
        $this->modelClass = Service::class;
        $this->entityName = 'Service';
        $this->viewPrefix = 'admin.services';
        $this->routePrefix = 'services';
    }

    /**
     * Get validation rules for service
     */
    protected function getValidationRules($id = null)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:services,name',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:5',
            'price' => 'required|numeric|min:0',
        ];
        
        // If updating, exclude the current service from unique check
        if ($id) {
            $rules['name'] .= ',' . $id;
        }
        
        return $rules;
    }
    
    /**
     * Get validation messages for service
     */
    protected function getValidationMessages()
    {
        return [
            'name.required' => 'The service name is required.',
            'name.unique' => 'This service name is already taken.',
            'duration.required' => 'The service duration is required.',
            'duration.integer' => 'The duration must be a whole number in minutes.',
            'duration.min' => 'The minimum duration is 5 minutes.',
            'price.required' => 'The service price is required.',
            'price.numeric' => 'The price must be a valid number.',
            'price.min' => 'The price cannot be negative.'
        ];
    }
    
    /**
     * Prepare data for storing a service
     */
    protected function prepareStoreData(Request $request)
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => $request->name,
            'description' => $request->description,
            'duration' => $request->duration,
            'price' => $request->price,
        ];
    }
    
    /**
     * Prepare data for updating a service
     */
    protected function prepareUpdateData(Request $request)
    {
        return [
            'name' => $request->name,
            'description' => $request->description,
            'duration' => $request->duration,
            'price' => $request->price,
        ];
    }

    /**
     * Display a listing of services.
     * Handles both view request and AJAX data request.
     */
    public function index(Request $request)
    {
        Log::debug('ServiceController@index method entered.');
        try {
            if ($request->ajax() || $request->wantsJson()) {
                Log::debug('AJAX request detected in ServiceController@index.', $request->all());
                $query = Service::query();
                
                // Apply search filter if provided
                if ($request->has('search') && !empty($request->search)) {
                    $searchTerm = '%' . $request->search . '%';
                    $query->where('name', 'like', $searchTerm)
                         ->orWhere('description', 'like', $searchTerm);
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
                    Log::debug('Including soft-deleted services.');
                }
                
                // Paginate results
                $perPage = $request->input('per_page', 10);
                $services = $query->paginate($perPage);
                
                // Log success and data before returning
                Log::debug('Services fetched successfully for AJAX request.', [
                    'count' => $services->count(),
                    'total' => $services->total(),
                    'currentPage' => $services->currentPage(),
                    'perPage' => $services->perPage(),
                ]);
                
                return response()->json($services);
            }
            
            Log::debug('Non-AJAX request detected, returning view.');
            // Return the view for non-AJAX requests
            return view('admin.services.index');
        } catch (Throwable $e) {
            Log::error('Error fetching services in ServiceController@index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error fetching services: ' . $e->getMessage(),
                    'error_details' => $e->getTraceAsString()
                ], 500);
            }
            
            return view('admin.services.index')->with('error', 'Error loading services. Please try again.');
        }
    }

    /**
     * Check if a service name already exists.
     * Used for real-time validation.
     */
    public function checkNameExists(Request $request)
    {
        $name = $request->input('name');
        $excludeUuid = $request->input('exclude_uuid');
        
        $query = Service::where('name', $name);
        
        // If we're editing, exclude the current service
        if ($excludeUuid) {
            $query->where('uuid', '!=', $excludeUuid);
        }
        
        $exists = $query->withTrashed()->exists();
        
        return response()->json([
            'exists' => $exists
        ]);
    }
}
