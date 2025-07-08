<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class ExportController extends Controller
{
    protected ExportService $exportService;

    /**
     * ExportController constructor.
     *
     * @param ExportService $exportService
     */
    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Export translations for a specific language.
     *
     * @OA\Get(
     *     path="/export/language/{languageCode}",
     *     summary="Export translations for a specific language",
     *     description="Returns translations as a key-value object for the specified language",
     *     tags={"Export"},
     *     @OA\Parameter(
     *         name="languageCode",
     *         in="path",
     *         description="Language code (e.g., en, fr, es)",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translations exported successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "welcome_message": "Welcome to our application",
     *                 "login_button": "Login",
     *                 "signup_button": "Sign Up"
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Language not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Language not found")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @param string $languageCode
     * @return JsonResponse
     */
    public function exportByLanguage(Request $request, string $languageCode): JsonResponse
    {
        $translations = $this->exportService->exportByLanguage($languageCode);

        if (empty($translations)) {
            return response()->json(['error' => 'Language not found'], 404);
        }

        return response()->json($translations);
    }

    /**
     * Export translations for all languages.
     *
     * @OA\Get(
     *     path="/export/all",
     *     summary="Export translations for all languages",
     *     description="Returns translations as a nested object with language codes as keys",
     *     tags={"Export"},
     *     @OA\Response(
     *         response=200,
     *         description="Translations exported successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "en": {
     *                     "welcome_message": "Welcome to our application",
     *                     "login_button": "Login"
     *                 },
     *                 "fr": {
     *                     "welcome_message": "Bienvenue dans notre application",
     *                     "login_button": "Connexion"
     *                 }
     *             }
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function exportAll(Request $request): JsonResponse
    {
        $allTranslations = $this->exportService->exportAll();
        return response()->json($allTranslations);
    }

    /**
     * Export translations filtered by tags.
     *
     * @OA\Get(
     *     path="/export/tags",
     *     summary="Export translations filtered by tags",
     *     description="Returns translations filtered by specified tags",
     *     tags={"Export"},
     *     @OA\Parameter(
     *         name="tags",
     *         in="query",
     *         description="Comma-separated list of tag names",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="language",
     *         in="query",
     *         description="Language code to filter by (optional)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translations exported successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "en": {
     *                     "mobile_welcome": "Welcome to our mobile app",
     *                     "mobile_login": "Login"
     *                 }
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function exportByTags(Request $request): JsonResponse
    {
        $request->validate([
            'tags' => 'required|string',
            'language' => 'nullable|string|exists:languages,code',
        ]);

        $tagNames = explode(',', $request->tags);
        $languageCode = $request->language;

        $translations = $this->exportService->exportByTags($tagNames, $languageCode);
        return response()->json($translations);
    }
}
