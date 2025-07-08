<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_export_translations_by_language()
    {
        // Create test data
        $language = Language::factory()->create(['code' => 'en']);
        
        $translations = [
            ['key' => 'greeting', 'content' => 'Hello'],
            ['key' => 'farewell', 'content' => 'Goodbye']
        ];
        
        foreach ($translations as $data) {
            Translation::factory()->create([
                'language_id' => $language->id,
                'key' => $data['key'],
                'content' => $data['content']
            ]);
        }

        // Make request
        $response = $this->getJson("/api/export/language/en");

        // Assert response
        $response->assertStatus(200);
        $response->assertJson([
            'greeting' => 'Hello',
            'farewell' => 'Goodbye'
        ]);
    }

    /** @test */
    public function it_returns_empty_array_for_nonexistent_language()
    {
        // Make request with non-existent language code
        $response = $this->getJson("/api/export/language/nonexistent");

        // Assert response
        $response->assertStatus(200);
        $response->assertJson([]);
    }

    /** @test */
    public function it_can_export_all_translations()
    {
        // Create test data
        $languages = [
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'fr', 'name' => 'French']
        ];
        
        $languageModels = [];
        foreach ($languages as $data) {
            $languageModels[$data['code']] = Language::factory()->create($data);
        }
        
        // Add translations for English
        Translation::factory()->create([
            'language_id' => $languageModels['en']->id,
            'key' => 'greeting',
            'content' => 'Hello'
        ]);
        
        // Add translations for French
        Translation::factory()->create([
            'language_id' => $languageModels['fr']->id,
            'key' => 'greeting',
            'content' => 'Bonjour'
        ]);

        // Make request
        $response = $this->getJson("/api/export/all");

        // Assert response
        $response->assertStatus(200);
        $response->assertJson([
            'en' => [
                'greeting' => 'Hello'
            ],
            'fr' => [
                'greeting' => 'Bonjour'
            ]
        ]);
    }

    /** @test */
    public function it_can_export_translations_by_tags()
    {
        // Create test data
        $language = Language::factory()->create(['code' => 'en']);
        $tag = Tag::factory()->create(['name' => 'common']);
        
        // Create translation with tag
        $translation = Translation::factory()->create([
            'language_id' => $language->id,
            'key' => 'greeting',
            'content' => 'Hello'
        ]);
        $translation->tags()->attach($tag->id);
        
        // Create translation without tag
        Translation::factory()->create([
            'language_id' => $language->id,
            'key' => 'farewell',
            'content' => 'Goodbye'
        ]);

        // Make request
        $response = $this->getJson("/api/export/tags?tags=common");

        // Assert response
        $response->assertStatus(200);
        $response->assertJson([
            'en' => [
                'greeting' => 'Hello'
            ]
        ]);
        
        // The farewell key should not be included
        $responseData = $response->json();
        $this->assertArrayNotHasKey('farewell', $responseData['en']);
    }

    /** @test */
    public function it_can_export_translations_by_tags_and_language()
    {
        // Create test data
        $languages = [
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'fr', 'name' => 'French']
        ];
        
        $languageModels = [];
        foreach ($languages as $data) {
            $languageModels[$data['code']] = Language::factory()->create($data);
        }
        
        $tag = Tag::factory()->create(['name' => 'common']);
        
        // Create English translation with tag
        $enTranslation = Translation::factory()->create([
            'language_id' => $languageModels['en']->id,
            'key' => 'greeting',
            'content' => 'Hello'
        ]);
        $enTranslation->tags()->attach($tag->id);
        
        // Create French translation with tag
        $frTranslation = Translation::factory()->create([
            'language_id' => $languageModels['fr']->id,
            'key' => 'greeting',
            'content' => 'Bonjour'
        ]);
        $frTranslation->tags()->attach($tag->id);

        // Make request with language filter
        $response = $this->getJson("/api/export/tags?tags=common&language=en");

        // Assert response
        $response->assertStatus(200);
        $response->assertJson([
            'en' => [
                'greeting' => 'Hello'
            ]
        ]);
        
        // French translations should not be included
        $responseData = $response->json();
        $this->assertArrayNotHasKey('fr', $responseData);
    }

    /** @test */
    public function it_returns_empty_array_for_nonexistent_tag()
    {
        // Make request with non-existent tag
        $response = $this->getJson("/api/export/tags?tags=nonexistent");

        // Assert response
        $response->assertStatus(200);
        $response->assertJson([]);
    }
} 