<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PerformanceOptimizationService
{
    /**
     * Apply performance optimizations to a query.
     *
     * @param Builder $query
     * @return Builder
     */
    public function optimizeQuery(Builder $query): Builder
    {
        // Use select to limit fields when possible
        if (empty($query->getQuery()->columns)) {
            $query->select('*');
        }

        // Add index hints for larger tables
        $this->addIndexHints($query);

        return $query;
    }

    /**
     * Add index hints to a query for better performance.
     *
     * @param Builder $query
     * @return void
     */
    private function addIndexHints(Builder $query): void
    {
        // Example: Add index hints based on the table being queried
        $table = $query->getModel()->getTable();
        
        if ($table === 'translations') {
            // This is a more advanced SQL optimization technique
            // Only use if you've verified it helps performance in your specific case
            $query->from(DB::raw("`{$table}` USE INDEX (primary, translations_language_id_key_unique)"));
        }
    }

    /**
     * Get data with cache optimization.
     *
     * @param string $key
     * @param \Closure $callback
     * @param int $minutes
     * @return mixed
     */
    public function rememberCache(string $key, \Closure $callback, int $minutes = 60): mixed
    {
        return Cache::remember($key, now()->addMinutes($minutes), $callback);
    }

    /**
     * Get data with forever cache optimization.
     * Use for data that rarely changes.
     *
     * @param string $key
     * @param \Closure $callback
     * @return mixed
     */
    public function rememberCacheForever(string $key, \Closure $callback): mixed
    {
        return Cache::rememberForever($key, $callback);
    }

    /**
     * Invalidate cache.
     *
     * @param string|array $keys
     * @return bool
     */
    public function invalidateCache(string|array $keys): bool
    {
        return Cache::forget($keys);
    }
    
    /**
     * Chunk database results for memory efficiency.
     *
     * @param Builder $query
     * @param int $chunkSize
     * @param \Closure $callback
     * @return bool
     */
    public function chunkResults(Builder $query, int $chunkSize, \Closure $callback): bool
    {
        return $query->chunk($chunkSize, $callback);
    }
} 