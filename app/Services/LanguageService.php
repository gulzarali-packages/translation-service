<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class LanguageService
{
    /**
     * Get all languages with optional pagination.
     *
     * @param bool $paginate
     * @param int $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getAllLanguages(bool $paginate = false, int $perPage = 15): Collection|LengthAwarePaginator
    {
        return $paginate 
            ? Language::paginate($perPage) 
            : Language::all();
    }

    /**
     * Create a new language.
     *
     * @param array $data
     * @return Language
     */
    public function createLanguage(array $data): Language
    {
        return Language::create($data);
    }

    /**
     * Update an existing language.
     *
     * @param Language $language
     * @param array $data
     * @return Language
     */
    public function updateLanguage(Language $language, array $data): Language
    {
        $language->update($data);
        return $language;
    }

    /**
     * Delete a language and all its associated translations.
     *
     * @param Language $language
     * @return bool
     */
    public function deleteLanguage(Language $language): bool
    {
        return $language->delete();
    }

    /**
     * Get active languages.
     *
     * @return Collection
     */
    public function getActiveLanguages(): Collection
    {
        return Language::where('is_active', true)->get();
    }

    /**
     * Get a language by its code.
     *
     * @param string $code
     * @return Language|null
     */
    public function getLanguageByCode(string $code): ?Language
    {
        return Language::where('code', $code)->first();
    }
} 