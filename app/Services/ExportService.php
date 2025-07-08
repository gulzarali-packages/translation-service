<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExportService
{
    protected PerformanceOptimizationService $performanceService;

    /**
     * ExportService constructor.
     *
     * @param PerformanceOptimizationService $performanceService
     */
    public function __construct(PerformanceOptimizationService $performanceService)
    {
        $this->performanceService = $performanceService;
    }

    /**
     * Export translations for a specific language.
     *
     * @param string $languageCode
     * @return array
     */
    public function exportByLanguage(string $languageCode): array
    {
        // Get the language by code
        $language = Language::where('code', $languageCode)->first();

        if (!$language) {
            return [];
        }

        // Generate cache key based on language and last updated translation
        $cacheKey = "translations_{$languageCode}_" . $this->getLastUpdatedTimestamp($language->id);

        // Try to get from cache first with performance optimizations
        return $this->performanceService->rememberCache($cacheKey, function () use ($language) {
            // Fetch translations with eager loading for performance
            return $this->getTranslationsForLanguage($language->id);
        }, 1440); // Cache for 24 hours (in minutes)
    }

    /**
     * Export translations for all languages.
     *
     * @return array
     */
    public function exportAll(): array
    {
        // Generate cache key based on the last updated translation across all languages
        $cacheKey = "translations_all_" . $this->getLastUpdatedTimestamp();

        // Try to get from cache first with performance optimizations
        return $this->performanceService->rememberCache($cacheKey, function () {
            $result = [];
            $languages = Language::where('is_active', true)->get();

            foreach ($languages as $language) {
                $result[$language->code] = $this->getTranslationsForLanguage($language->id);
            }

            return $result;
        }, 1440); // Cache for 24 hours (in minutes)
    }

    /**
     * Export translations filtered by tags.
     *
     * @param array $tagNames
     * @param string|null $languageCode
     * @return array
     */
    public function exportByTags(array $tagNames, ?string $languageCode = null): array
    {
        // Generate cache key
        $cacheKey = "translations_tags_" . md5(implode(',', $tagNames) . '_' . ($languageCode ?? 'all'));

        // Try to get from cache first with performance optimizations
        return $this->performanceService->rememberCache($cacheKey, function () use ($tagNames, $languageCode) {
            $query = Translation::with('language')
                ->select('translations.*')
                ->join('translation_tag', 'translations.id', '=', 'translation_tag.translation_id')
                ->join('tags', 'translation_tag.tag_id', '=', 'tags.id')
                ->whereIn('tags.name', $tagNames);

            if ($languageCode) {
                $query->whereHas('language', function ($query) use ($languageCode) {
                    $query->where('code', $languageCode);
                });
            }

            // Apply performance optimizations
            $query = $this->performanceService->optimizeQuery($query);
            $results = $query->get();

            // Format the results
            $formatted = [];
            foreach ($results as $translation) {
                $langCode = $translation->language->code;
                if (!isset($formatted[$langCode])) {
                    $formatted[$langCode] = [];
                }
                $formatted[$langCode][$translation->key] = $translation->content;
            }

            return $formatted;
        }, 1440); // Cache for 24 hours (in minutes)
    }

    /**
     * Get the timestamp of the last updated translation for a specific language.
     *
     * @param int|null $languageId
     * @return string
     */
    private function getLastUpdatedTimestamp(?int $languageId = null): string
    {
        // Cache this operation as it's potentially expensive but doesn't change often
        $cacheKey = "last_updated_" . ($languageId ?? 'all');
        
        return $this->performanceService->rememberCache($cacheKey, function () use ($languageId) {
            $query = Translation::select(DB::raw('MAX(updated_at) as last_updated'));

            if ($languageId) {
                $query->where('language_id', $languageId);
            }

            $result = $query->first();
            return $result ? $result->last_updated : now()->toDateTimeString();
        }, 60); // Cache for 1 hour (in minutes)
    }

    /**
     * Get translations for a specific language formatted as a key-value object.
     *
     * @param int $languageId
     * @return array
     */
    private function getTranslationsForLanguage(int $languageId): array
    {
        $query = Translation::where('language_id', $languageId)
            ->select('key', 'content');

        // Apply performance optimizations
        $query = $this->performanceService->optimizeQuery($query);
        
        $translations = $query->get();

        $result = [];
        foreach ($translations as $translation) {
            $result[$translation->key] = $translation->content;
        }

        return $result;
    }
} 