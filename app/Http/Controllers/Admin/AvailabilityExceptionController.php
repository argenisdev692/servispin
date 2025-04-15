<?php

namespace App\Http\Controllers\Admin;

use App\Models\AvailabilityException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Services\TransactionService;
use App\Http\Controllers\BaseCrudController;
use Carbon\Carbon;

class AvailabilityExceptionController extends BaseCrudController
{
    public function __construct(TransactionService $transactionService)
    {
        parent::__construct($transactionService);
        $this->modelClass = AvailabilityException::class;
        $this->entityName = 'Availability Exception';
        $this->viewPrefix = 'admin.availability-exceptions';
        $this->routePrefix = 'admin.availability-exceptions';
    }

    /**
     * Get validation rules for availability exception
     */
    protected function getValidationRules($id = null)
    {
        $rules = [
            'date' => 'required|date',
            'is_available' => 'required|boolean',
            'reason' => 'required|string|max:255'
        ];
        
        // If updating, need to check uniqueness of date
        if ($id) {
            $rules['date'] .= '|unique:availability_exceptions,date,' . $id;
        } else {
            $rules['date'] .= '|unique:availability_exceptions,date';
        }
        
        return $rules;
    }
    
    /**
     * Get validation messages for availability exception
     */
    protected function getValidationMessages()
    {
        return [
            'date.required' => 'The date is required.',
            'date.date' => 'The date must be a valid date.',
            'date.unique' => 'An exception for this date already exists.',
            'is_available.required' => 'The availability status is required.',
            'is_available.boolean' => 'The availability status must be a boolean value.',
            'reason.required' => 'The reason is required.',
            'reason.max' => 'The reason cannot exceed 255 characters.'
        ];
    }
    
    /**
     * Prepare data for storing an availability exception
     */
    protected function prepareStoreData(Request $request)
    {
        return [
            'uuid' => (string) Str::uuid(),
            'date' => Carbon::parse($request->date)->format('Y-m-d'),
            'is_available' => $request->is_available == 'true' || $request->is_available == 1,
            'reason' => $request->reason,
        ];
    }
    
    /**
     * Prepare data for updating an availability exception
     */
    protected function prepareUpdateData(Request $request)
    {
        return [
            'date' => Carbon::parse($request->date)->format('Y-m-d'),
            'is_available' => $request->is_available == 'true' || $request->is_available == 1,
            'reason' => $request->reason,
        ];
    }

    /**
     * Display a listing of availability exceptions.
     * Handles both view request and AJAX data request.
     */
    public function index(Request $request)
    {
        Log::debug('AvailabilityExceptionController@index method entered.');
        try {
            if ($request->ajax() || $request->wantsJson()) {
                Log::debug('AJAX request detected in AvailabilityExceptionController@index.', $request->all());
                $query = AvailabilityException::query();
                
                // Apply search filter if provided
                if ($request->has('search') && !empty($request->search)) {
                    $searchTerm = '%' . $request->search . '%';
                    $query->where('reason', 'like', $searchTerm);
                    Log::debug('Applying search filter.', ['term' => $request->search]);
                }
                
                // Apply date range filter if provided
                if ($request->has('date_start') && !empty($request->date_start)) {
                    $query->whereDate('date', '>=', $request->date_start);
                    Log::debug('Applying date start filter.', ['date_start' => $request->date_start]);
                }
                
                if ($request->has('date_end') && !empty($request->date_end)) {
                    $query->whereDate('date', '<=', $request->date_end);
                    Log::debug('Applying date end filter.', ['date_end' => $request->date_end]);
                }
                
                // Apply availability filter if provided
                if ($request->has('is_available') && $request->is_available !== null) {
                    $query->where('is_available', $request->is_available === 'true' || $request->is_available === '1');
                    Log::debug('Applying availability filter.', ['is_available' => $request->is_available]);
                }
                
                // Apply sorting
                $sortField = $request->input('sort_field', 'date');
                $sortDirection = $request->input('sort_direction', 'desc');
                $query->orderBy($sortField, $sortDirection);
                Log::debug('Applying sorting.', ['field' => $sortField, 'direction' => $sortDirection]);
                
                // Show deleted items if requested
                if ($request->has('show_deleted') && $request->show_deleted === 'true') {
                    $query->withTrashed();
                    Log::debug('Including soft-deleted availability exceptions.');
                }
                
                // Paginate results
                $perPage = $request->input('per_page', 10);
                $exceptions = $query->paginate($perPage);
                
                // Log success and data before returning
                Log::debug('Availability exceptions fetched successfully for AJAX request.', [
                    'count' => $exceptions->count(),
                    'total' => $exceptions->total(),
                    'currentPage' => $exceptions->currentPage(),
                    'perPage' => $exceptions->perPage(),
                ]);
                
                return response()->json($exceptions);
            }
            
            Log::debug('Non-AJAX request detected, returning view.');
            // Return the view for non-AJAX requests
            return view('admin.availability-exceptions.index');
        } catch (Throwable $e) {
            Log::error('Error fetching availability exceptions in AvailabilityExceptionController@index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error fetching availability exceptions: ' . $e->getMessage(),
                    'error_details' => $e->getTraceAsString()
                ], 500);
            }
            
            return view('admin.availability-exceptions.index')->with('error', 'Error loading availability exceptions. Please try again.');
        }
    }

    /**
     * Check if a date already has an exception
     * Used for real-time validation.
     */
    public function checkDateExists(Request $request)
    {
        $date = $request->input('date');
        $excludeUuid = $request->input('exclude_uuid');
        
        $query = AvailabilityException::whereDate('date', $date);
        
        // If we're editing, exclude the current exception
        if ($excludeUuid) {
            $query->where('uuid', '!=', $excludeUuid);
        }
        
        $exists = $query->withTrashed()->exists();
        
        return response()->json([
            'exists' => $exists
        ]);
    }
}
