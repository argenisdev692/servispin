<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CompanyData; // Make sure to import your CompanyData model
use Illuminate\Support\Facades\View; // Import the View facade

class ShareCompanyData
{
    /**
     * Handle an incoming request.
     *
     * Fetches the first CompanyData record and shares it with all views.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Fetch the first (and likely only) record from the company_data table.
        // Use caching in production for better performance if this data doesn't change often.
        $companyData = CompanyData::first();

        // Share the data with all views under the variable name 'companyData'.
        // Views can now access this object, e.g., $companyData->company_name
        View::share('companyData', $companyData);

        // Continue processing the request.
        return $next($request);
    }
} 