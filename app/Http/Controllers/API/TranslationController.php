<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\TranslationRequest;
use App\Http\Resources\TranslationResource;
use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TranslationController extends Controller
{
    protected TranslationService $translationService;

    /**
     * TranslationController constructor.
     *
     * @param TranslationService $translationService
     */
    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/translations",
     *     summary="Get a list of translations",
     *     tags={"Translations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="language_id",
     *         in="query",
     *         description="Filter by language ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="tag",
     *         in="query",
     *         description="Filter by tag name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="key",
     *         in="query",
     *         description="Filter by key (partial match)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="Filter by content (partial match)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of translations",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="language_id", type="integer", example=1),
     *                 @OA\Property(property="key", type="string", example="welcome_message"),
     *                 @OA\Property(property="content", type="string", example="Welcome to our application"),
     *                 @OA\Property(property="language", type="object"),
     *                 @OA\Property(property="tags", type="array", @OA\Items(type="object"))
     *             )),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['language_id', 'tag', 'key', 'content']);
        $translations = $this->translationService->getFilteredTranslations(
            $filters, 
            $request->per_page ?? 15
        );

        return TranslationResource::collection($translations);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/translations",
     *     summary="Create a new translation",
     *     tags={"Translations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"language_id", "key", "content"},
     *             @OA\Property(property="language_id", type="integer", example=1),
     *             @OA\Property(property="key", type="string", example="welcome_message"),
     *             @OA\Property(property="content", type="string", example="Welcome to our application"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="integer"), example={1, 2})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Translation created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="language_id", type="integer", example=1),
     *                 @OA\Property(property="key", type="string", example="welcome_message"),
     *                 @OA\Property(property="content", type="string", example="Welcome to our application"),
     *                 @OA\Property(property="language", type="object"),
     *                 @OA\Property(property="tags", type="array", @OA\Items(type="object"))
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
     * @param TranslationRequest $request
     * @return TranslationResource
     */
    public function store(TranslationRequest $request): TranslationResource
    {
        $translation = $this->translationService->createTranslation($request->validated());
        return new TranslationResource($translation);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/translations/{id}",
     *     summary="Get a specific translation",
     *     tags={"Translations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Translation ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translation details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="language_id", type="integer", example=1),
     *                 @OA\Property(property="key", type="string", example="welcome_message"),
     *                 @OA\Property(property="content", type="string", example="Welcome to our application"),
     *                 @OA\Property(property="language", type="object"),
     *                 @OA\Property(property="tags", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Translation not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     *
     * @param Translation $translation
     * @return TranslationResource
     */
    public function show(Translation $translation): TranslationResource
    {
        return new TranslationResource($translation->load('tags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/translations/{id}",
     *     summary="Update a translation",
     *     tags={"Translations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Translation ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="language_id", type="integer", example=1),
     *             @OA\Property(property="key", type="string", example="welcome_message"),
     *             @OA\Property(property="content", type="string", example="Welcome to our application"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="integer"), example={1, 2})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translation updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="language_id", type="integer", example=1),
     *                 @OA\Property(property="key", type="string", example="welcome_message"),
     *                 @OA\Property(property="content", type="string", example="Welcome to our application"),
     *                 @OA\Property(property="language", type="object"),
     *                 @OA\Property(property="tags", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Translation not found"
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
     * @param TranslationRequest $request
     * @param Translation $translation
     * @return TranslationResource
     */
    public function update(TranslationRequest $request, Translation $translation): TranslationResource
    {
        $updatedTranslation = $this->translationService->updateTranslation($translation, $request->validated());
        return new TranslationResource($updatedTranslation);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/translations/{id}",
     *     summary="Delete a translation",
     *     tags={"Translations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Translation ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translation deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Translation deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Translation not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     *
     * @param Translation $translation
     * @return JsonResponse
     */
    public function destroy(Translation $translation): JsonResponse
    {
        $translation->delete();
        return response()->json(['message' => 'Translation deleted successfully']);
    }

    /**
     * Search translations.
     *
     * @OA\Get(
     *     path="/translations/search",
     *     summary="Search translations",
     *     tags={"Translations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="key",
     *         in="query",
     *         description="Search by key (partial match)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="Search by content (partial match)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="language_id",
     *         in="query",
     *         description="Filter by language ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="tags",
     *         in="query",
     *         description="Filter by tag IDs (comma-separated)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="language_id", type="integer", example=1),
     *                 @OA\Property(property="key", type="string", example="welcome_message"),
     *                 @OA\Property(property="content", type="string", example="Welcome to our application"),
     *                 @OA\Property(property="language", type="object"),
     *                 @OA\Property(property="tags", type="array", @OA\Items(type="object"))
     *             )),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $searchParams = $request->only(['key', 'content', 'language_id', 'tags']);
        $translations = $this->translationService->searchTranslations(
            $searchParams, 
            $request->per_page ?? 15
        );

        return TranslationResource::collection($translations);
    }
}
