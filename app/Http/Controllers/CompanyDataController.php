<?php

namespace App\Http\Controllers;

use App\Models\CompanyData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CompanyDataController extends Controller
{
    /**
     * Display the company data (usually just one record).
     * Handles AJAX request to get data for the view/table.
     */
    public function index()
    {
        $companyData = CompanyData::first();
        if (request()->expectsJson()) {
            return response()->json(['companyData' => $companyData]);
        }
        return view('admin.company-data.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * Handles AJAX POST request from the modal form.
     */
    public function store(Request $request)
    {
        $companyData = CompanyData::first();

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'address_google_map' => 'nullable|url',
            'website' => 'nullable|url|max:255',
            'social_media_facebook' => 'nullable|url|max:255',
            'social_media_instagram' => 'nullable|url|max:255',
            'social_media_twitter' => 'nullable|url|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if ($companyData) {
            $companyData->update($data);
        } else {
            $data['uuid'] = (string) Str::uuid();
            $data['user_id'] = auth()->id() ?? 1;
            $companyData = CompanyData::create($data);
        }

        return response()->json([
            'message' => 'Company data saved successfully!',
            'companyData' => $companyData
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(CompanyData $companyData)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * Handles AJAX GET request to fetch data for the edit modal.
     */
    public function edit(CompanyData $companyData)
    {
        $dataToEdit = $companyData ?? CompanyData::first();

        if (!$dataToEdit) {
            return response()->json(['message' => 'Company data not found.'], 404);
        }

        return response()->json($dataToEdit);
    }

    /**
     * Update the specified resource in storage.
     * Handles AJAX PUT/PATCH request from the modal form.
     * Note: Our 'store' method effectively handles updates too for this singleton case.
     *       You could potentially redirect update requests to store or keep this separate.
     *       Let's make it explicit here for clarity, reusing validation logic.
     */
    public function update(Request $request, CompanyData $companyData)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'address_google_map' => 'nullable|url',
            'website' => 'nullable|url|max:255',
            'social_media_facebook' => 'nullable|url|max:255',
            'social_media_instagram' => 'nullable|url|max:255',
            'social_media_twitter' => 'nullable|url|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $companyData->update($validator->validated());

        return response()->json([
            'message' => 'Company data updated successfully!',
            'companyData' => $companyData
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * Handles AJAX DELETE request.
     * NOTE: Deleting the single company data record might not be desirable.
     * Consider disabling this route/functionality or adding confirmation.
     */
    public function destroy(CompanyData $companyData)
    {
        $companyData->delete();

        return response()->json(['message' => 'Company data deleted successfully!']);
    }
}
