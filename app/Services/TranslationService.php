<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TranslationService
{
    protected PerformanceOptimizationService $performanceService;

    /**
     * TranslationService constructor.
     *
     * @param PerformanceOptimizationService $performanceService
     */
    public function __construct(PerformanceOptimizationService $performanceService)
    {
        $this->performanceService = $performanceService;
    }

    /**
     * Get filtered translations based on request parameters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getFilteredTranslations(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Translation::with(['language', 'tags']);

        // Filter by language
        if (isset($filters['language_id'])) {
            $query->where('language_id', $filters['language_id']);
        }

        // Filter by tag
        if (isset($filters['tag'])) {
            $query->withTag($filters['tag']);
        }

        // Filter by key
        if (isset($filters['key'])) {
            $query->byKey($filters['key']);
        }

        // Filter by content
        if (isset($filters['content'])) {
            $query->byContent($filters['content']);
        }

        // Apply performance optimizations
        $query = $this->performanceService->optimizeQuery($query);

        // Use pagination for better performance
        return $query->paginate($perPage);
    }

    /**
     * Create a new translation.
     *
     * @param array $data
     * @return Translation
     */
    public function createTranslation(array $data): Translation
    {
        $translation = Translation::create($data);

        // Attach tags if provided
        if (isset($data['tags'])) {
            $translation->tags()->attach($data['tags']);
        }

        // Invalidate related caches
        $this->invalidateCaches($translation->language_id);

        return $translation->load('tags');
    }

    /**
     * Update an existing translation.
     *
     * @param Translation $translation
     * @param array $data
     * @return Translation
     */
    public function updateTranslation(Translation $translation, array $data): Translation
    {
        $translation->update($data);

        // Sync tags if provided
        if (isset($data['tags'])) {
            $translation->tags()->sync($data['tags']);
        }

        // Invalidate related caches
        $this->invalidateCaches($translation->language_id);

        return $translation->load('tags');
    }

    /**
     * Search translations with advanced filtering.
     *
     * @param array $searchParams
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchTranslations(array $searchParams, int $perPage = 15): LengthAwarePaginator
    {
        $query = Translation::query()->with(['language', 'tags']);

        // Advanced search with multiple conditions
        $query->where(function (Builder $query) use ($searchParams) {
            // Search by key
            if (isset($searchParams['key'])) {
                $query->where('key', 'like', "%{$searchParams['key']}%");
            }

            // Search by content
            if (isset($searchParams['content'])) {
                $query->orWhere('content', 'like', "%{$searchParams['content']}%");
            }
        });

        // Filter by language
        if (isset($searchParams['language_id'])) {
            $query->where('language_id', $searchParams['language_id']);
        }

        // Filter by tags
        if (isset($searchParams['tags'])) {
            $tagIds = explode(',', $searchParams['tags']);
            $query->whereHas('tags', function ($query) use ($tagIds) {
                $query->whereIn('id', $tagIds);
            });
        }

        // Apply performance optimizations
        $query = $this->performanceService->optimizeQuery($query);

        // Use pagination for better performance
        return $query->paginate($perPage);
    }

    /**
     * Invalidate translation-related caches.
     *
     * @param int|null $languageId
     * @return void
     */
    private function invalidateCaches(?int $languageId = null): void
    {
        // Generate the appropriate cache keys
        $cacheKeys = [
            "translations_all_*",
        ];

        if ($languageId) {
            $language = \App\Models\Language::find($languageId);
            if ($language) {
                $cacheKeys[] = "translations_{$language->code}_*";
            }
        }

        // Invalidate cache for each key
        foreach ($cacheKeys as $key) {
            $this->performanceService->invalidateCache($key);
        }
    }
} 