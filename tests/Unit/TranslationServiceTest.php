<?php

namespace Tests\Unit;

use App\Models\Language;
use App\Models\Tag;
use App\Models\Translation;
use App\Services\PerformanceOptimizationService;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;

class TranslationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TranslationService $translationService;
    protected $mockPerformanceService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockPerformanceService = Mockery::mock(PerformanceOptimizationService::class);
        $this->mockPerformanceService->shouldReceive('optimizeQuery')->andReturnUsing(function ($query) {
            return $query;
        });
        $this->mockPerformanceService->shouldReceive('invalidateCache')->andReturn(true);
        
        $this->translationService = new TranslationService($this->mockPerformanceService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_get_filtered_translations()
    {
        // Create test data
        $language = Language::factory()->create();
        $tag = Tag::factory()->create();
        $translation = Translation::factory()->create([
            'language_id' => $language->id,
            'key' => 'test.key',
            'content' => 'Test content'
        ]);
        $translation->tags()->attach($tag->id);

        // Test filtering by language
        $result = $this->translationService->getFilteredTranslations(['language_id' => $language->id]);
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(1, $result->count());
        $this->assertEquals($translation->id, $result->first()->id);

        // Test filtering by key
        $result = $this->translationService->getFilteredTranslations(['key' => 'test.key']);
        $this->assertEquals(1, $result->count());
        $this->assertEquals($translation->id, $result->first()->id);

        // Test filtering by content
        $result = $this->translationService->getFilteredTranslations(['content' => 'Test content']);
        $this->assertEquals(1, $result->count());
        $this->assertEquals($translation->id, $result->first()->id);
    }

    /** @test */
    public function it_can_create_translation()
    {
        $language = Language::factory()->create();
        $tag = Tag::factory()->create();

        $data = [
            'language_id' => $language->id,
            'key' => 'new.key',
            'content' => 'New content',
            'tags' => [$tag->id]
        ];

        $translation = $this->translationService->createTranslation($data);

        $this->assertDatabaseHas('translations', [
            'language_id' => $language->id,
            'key' => 'new.key',
            'content' => 'New content'
        ]);

        $this->assertDatabaseHas('translation_tag', [
            'translation_id' => $translation->id,
            'tag_id' => $tag->id
        ]);
    }

    /** @test */
    public function it_can_update_translation()
    {
        $language = Language::factory()->create();
        $tag = Tag::factory()->create();
        $newTag = Tag::factory()->create();
        
        $translation = Translation::factory()->create([
            'language_id' => $language->id,
            'key' => 'test.key',
            'content' => 'Test content'
        ]);
        $translation->tags()->attach($tag->id);

        $data = [
            'key' => 'updated.key',
            'content' => 'Updated content',
            'tags' => [$newTag->id]
        ];

        $updatedTranslation = $this->translationService->updateTranslation($translation, $data);

        $this->assertDatabaseHas('translations', [
            'id' => $translation->id,
            'key' => 'updated.key',
            'content' => 'Updated content'
        ]);

        $this->assertDatabaseHas('translation_tag', [
            'translation_id' => $translation->id,
            'tag_id' => $newTag->id
        ]);

        $this->assertDatabaseMissing('translation_tag', [
            'translation_id' => $translation->id,
            'tag_id' => $tag->id
        ]);
    }

    /** @test */
    public function it_can_search_translations()
    {
        // Create test data
        $language = Language::factory()->create();
        $tag = Tag::factory()->create();
        $translation = Translation::factory()->create([
            'language_id' => $language->id,
            'key' => 'search.key',
            'content' => 'Searchable content'
        ]);
        $translation->tags()->attach($tag->id);

        // Test searching by key
        $result = $this->translationService->searchTranslations(['key' => 'search']);
        $this->assertEquals(1, $result->count());
        $this->assertEquals($translation->id, $result->first()->id);

        // Test searching by content
        $result = $this->translationService->searchTranslations(['content' => 'Searchable']);
        $this->assertEquals(1, $result->count());
        $this->assertEquals($translation->id, $result->first()->id);

        // Test searching by language
        $result = $this->translationService->searchTranslations(['language_id' => $language->id]);
        $this->assertEquals(1, $result->count());
        $this->assertEquals($translation->id, $result->first()->id);

        // Test searching by tags
        $result = $this->translationService->searchTranslations(['tags' => $tag->id]);
        $this->assertEquals(1, $result->count());
        $this->assertEquals($translation->id, $result->first()->id);
    }
} 