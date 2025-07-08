<?php

namespace Tests\Unit;

use App\Models\Language;
use App\Models\Tag;
use App\Models\Translation;
use App\Services\ExportService;
use App\Services\PerformanceOptimizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class ExportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ExportService $exportService;
    protected $mockPerformanceService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockPerformanceService = Mockery::mock(PerformanceOptimizationService::class);
        $this->mockPerformanceService->shouldReceive('optimizeQuery')->andReturnUsing(function ($query) {
            return $query;
        });
        
        // Setup cache mocking
        $this->mockPerformanceService->shouldReceive('rememberCache')
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });
        
        $this->exportService = new ExportService($this->mockPerformanceService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_export_by_language()
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

        // Test export
        $result = $this->exportService->exportByLanguage('en');
        
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Hello', $result['greeting']);
        $this->assertEquals('Goodbye', $result['farewell']);
    }

    /** @test */
    public function it_returns_empty_array_for_nonexistent_language()
    {
        $result = $this->exportService->exportByLanguage('nonexistent');
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function it_can_export_all_languages()
    {
        // Create test data
        $languages = [
            ['code' => 'en', 'name' => 'English', 'is_active' => true],
            ['code' => 'fr', 'name' => 'French', 'is_active' => true],
            ['code' => 'de', 'name' => 'German', 'is_active' => false]
        ];
        
        $languageModels = [];
        foreach ($languages as $data) {
            $languageModels[$data['code']] = Language::factory()->create($data);
        }
        
        // Add translations
        Translation::factory()->create([
            'language_id' => $languageModels['en']->id,
            'key' => 'greeting',
            'content' => 'Hello'
        ]);
        
        Translation::factory()->create([
            'language_id' => $languageModels['fr']->id,
            'key' => 'greeting',
            'content' => 'Bonjour'
        ]);
        
        Translation::factory()->create([
            'language_id' => $languageModels['de']->id,
            'key' => 'greeting',
            'content' => 'Hallo'
        ]);

        // Test export all
        $result = $this->exportService->exportAll();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('en', $result);
        $this->assertArrayHasKey('fr', $result);
        
        // Inactive languages should still be included
        $this->assertArrayHasKey('de', $result);
        
        $this->assertEquals('Hello', $result['en']['greeting']);
        $this->assertEquals('Bonjour', $result['fr']['greeting']);
        $this->assertEquals('Hallo', $result['de']['greeting']);
    }

    /** @test */
    public function it_can_export_by_tags()
    {
        // Create test data
        $language = Language::factory()->create(['code' => 'en']);
        $tag = Tag::factory()->create(['name' => 'common']);
        
        $translation = Translation::factory()->create([
            'language_id' => $language->id,
            'key' => 'greeting',
            'content' => 'Hello'
        ]);
        
        $translation->tags()->attach($tag->id);
        
        // Create another translation without the tag
        Translation::factory()->create([
            'language_id' => $language->id,
            'key' => 'farewell',
            'content' => 'Goodbye'
        ]);

        // Test export by tag
        $result = $this->exportService->exportByTags(['common']);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('en', $result);
        $this->assertArrayHasKey('greeting', $result['en']);
        $this->assertEquals('Hello', $result['en']['greeting']);
        
        // The farewell key should not be included as it doesn't have the tag
        $this->assertArrayNotHasKey('farewell', $result['en']);
    }

    /** @test */
    public function it_can_export_by_tags_and_language()
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
        
        // Create translations for both languages
        $enTranslation = Translation::factory()->create([
            'language_id' => $languageModels['en']->id,
            'key' => 'greeting',
            'content' => 'Hello'
        ]);
        
        $frTranslation = Translation::factory()->create([
            'language_id' => $languageModels['fr']->id,
            'key' => 'greeting',
            'content' => 'Bonjour'
        ]);
        
        // Attach tag to both translations
        $enTranslation->tags()->attach($tag->id);
        $frTranslation->tags()->attach($tag->id);

        // Test export by tag and language
        $result = $this->exportService->exportByTags(['common'], 'en');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('en', $result);
        $this->assertArrayNotHasKey('fr', $result);
        $this->assertEquals('Hello', $result['en']['greeting']);
    }
} 