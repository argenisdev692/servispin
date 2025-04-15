<?php
namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait CacheTrait
{
    /**
     * Generate a cache key based on model and query parameters.
     *
     * @param string $modelName Name of the model
     * @param int|null $page Current page number
     * @param bool|null $includeTrashed Whether to include soft-deleted records
     * @return string
     */
    protected function generateCacheKey($modelName, $page = null, $includeTrashed = null)
    {
        $page = $page ?? request()->query('page', 1);
        $includeTrashed = $includeTrashed ?? $this->showDeleted;

        return strtolower($modelName) . '_' .
               $this->search . '_' .
               $this->sortField . '_' .
               $this->sortDirection . '_' .
               $this->perPage . '_' .
               $page . '_' .
               ($includeTrashed ? 'with_deleted' : 'active');
    }

    /**
     * Clear cache for the specified model.
     *
     * @param string $modelName Name of the model
     */
    protected function clearCache($modelName)
    {
        $currentPage = request()->query('page', 1);

        Cache::forget($this->generateCacheKey($modelName, $currentPage));
        Cache::forget(strtolower($modelName) . '_count_' . $this->search);
        Cache::forget($this->generateCacheKey($modelName, $currentPage - 1));
        Cache::forget($this->generateCacheKey($modelName, $currentPage + 1));

        if ($this->significantDataChange) {
            for ($i = 1; $i <= 5; $i++) {
                Cache::forget($this->generateCacheKey($modelName, $i));
                Cache::forget($this->generateCacheKey($modelName, $i, true));
            }
        }
    }
}