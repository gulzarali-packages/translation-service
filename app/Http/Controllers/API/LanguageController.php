<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\LanguageRequest;
use App\Http\Resources\LanguageResource;
use App\Models\Language;
use App\Services\LanguageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

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
     * @OA\Get(
     *     path="/languages",
     *     summary="Get all languages",
     *     tags={"Languages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of languages",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="English"),
     *                     @OA\Property(property="code", type="string", example="en"),
     *                     @OA\Property(property="is_default", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
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
     * @OA\Post(
     *     path="/languages",
     *     summary="Create a new language",
     *     tags={"Languages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "code"},
     *             @OA\Property(property="name", type="string", example="English"),
     *             @OA\Property(property="code", type="string", example="en"),
     *             @OA\Property(property="is_default", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Language created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="English"),
     *                 @OA\Property(property="code", type="string", example="en"),
     *                 @OA\Property(property="is_default", type="boolean", example=false),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
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
     * @OA\Get(
     *     path="/languages/{language}",
     *     summary="Get language details",
     *     tags={"Languages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="language",
     *         in="path",
     *         required=true,
     *         description="Language ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Language details",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="English"),
     *                 @OA\Property(property="code", type="string", example="en"),
     *                 @OA\Property(property="is_default", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Language not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
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
     * @OA\Put(
     *     path="/languages/{language}",
     *     summary="Update language",
     *     tags={"Languages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="language",
     *         in="path",
     *         required=true,
     *         description="Language ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="English"),
     *             @OA\Property(property="code", type="string", example="en"),
     *             @OA\Property(property="is_default", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Language updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="English"),
     *                 @OA\Property(property="code", type="string", example="en"),
     *                 @OA\Property(property="is_default", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Language not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
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
     * @OA\Delete(
     *     path="/languages/{language}",
     *     summary="Delete language",
     *     tags={"Languages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="language",
     *         in="path",
     *         required=true,
     *         description="Language ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Language deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Language deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Language not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
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
