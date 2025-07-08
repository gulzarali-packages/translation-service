<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\LanguageRequest;
use App\Http\Resources\LanguageResource;
use App\Models\Language;
use App\Services\LanguageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LanguageController extends Controller
{
    protected LanguageService $languageService;

    /**
     * LanguageController constructor.
     *
     * @param LanguageService $languageService
     */
    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $languages = $this->languageService->getAllLanguages();
        return LanguageResource::collection($languages);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param LanguageRequest $request
     * @return LanguageResource
     */
    public function store(LanguageRequest $request): LanguageResource
    {
        $language = $this->languageService->createLanguage($request->validated());
        return new LanguageResource($language);
    }

    /**
     * Display the specified resource.
     *
     * @param Language $language
     * @return LanguageResource
     */
    public function show(Language $language): LanguageResource
    {
        return new LanguageResource($language);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param LanguageRequest $request
     * @param Language $language
     * @return LanguageResource
     */
    public function update(LanguageRequest $request, Language $language): LanguageResource
    {
        $language = $this->languageService->updateLanguage($language, $request->validated());
        return new LanguageResource($language);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Language $language
     * @return JsonResponse
     */
    public function destroy(Language $language): JsonResponse
    {
        $this->languageService->deleteLanguage($language);
        return response()->json(['message' => 'Language deleted successfully']);
    }
}
