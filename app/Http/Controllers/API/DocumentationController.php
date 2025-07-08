<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentationController extends Controller
{
    /**
     * Get API information.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function info(Request $request): JsonResponse
    {
        return response()->json([
            'name' => 'Translation Service API',
            'version' => '1.0.0',
            'description' => 'API for managing translations and languages',
            'documentation' => url('api/documentation'),
            'endpoints' => [
                'authentication' => [
                    'login' => url('api/login'),
                    'logout' => url('api/logout'),
                    'user' => url('api/user')
                ],
                'languages' => url('api/languages'),
                'tags' => url('api/tags'),
                'translations' => url('api/translations'),
                'export' => [
                    'by_language' => url('api/export/language/{languageCode}'),
                    'all' => url('api/export/all'),
                    'by_tags' => url('api/export/tags')
                ]
            ]
        ]);
    }
} 