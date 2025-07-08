<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use App\Services\ExportService;
use App\Services\PerformanceOptimizationService;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected TranslationService $translationService;
    protected ExportService $exportService;
    protected PerformanceOptimizationService $performanceService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->performanceService = new PerformanceOptimizationService();
        $this->translationService = new TranslationService($this->performanceService);
        $this->exportService = new ExportService($this->performanceService);
        
        // Create and authenticate a user for protected routes
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        // Clear cache before each test
        Cache::flush();
    }

    /** @test */
    public function it_measures_performance_of_large_translation_list()
    {
        // Create test data - large number of translations
        $language = Language::factory()->create(['code' => 'en']);
        $translations = Translation::factory()->count(100)->create([
            'language_id' => $language->id
        ]);

        // Measure time to retrieve all translations
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/translations?per_page=50');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Assert response
        $response->assertStatus(200);
        
        // Performance assertion - should be under 1 second for this test load
        $this->assertLessThan(1.0, $executionTime, "Listing translations took too long: {$executionTime} seconds");
        
        // Log the performance result
        $this->addToAssertionCount(1);
        echo "\nTranslation listing execution time: {$executionTime} seconds";
    }

    /** @test */
    public function it_measures_performance_of_search_operation()
    {
        // Create test data with a specific pattern to search for
        $language = Language::factory()->create();
        
        // Create 100 translations with a searchable pattern in some of them
        for ($i = 0; $i < 100; $i++) {
            $key = ($i % 5 == 0) ? "searchable.key.{$i}" : "regular.key.{$i}";
            $content = ($i % 10 == 0) ? "This is searchable content {$i}" : "Regular content {$i}";
            
            Translation::factory()->create([
                'language_id' => $language->id,
                'key' => $key,
                'content' => $content
            ]);
        }

        // Measure time for search operation
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/translations/search?key=searchable&content=searchable');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Assert response
        $response->assertStatus(200);
        
        // Performance assertion - should be under 1 second
        $this->assertLessThan(1.0, $executionTime, "Search operation took too long: {$executionTime} seconds");
        
        // Log the performance result
        $this->addToAssertionCount(1);
        echo "\nSearch operation execution time: {$executionTime} seconds";
    }

    /** @test */
    public function it_measures_performance_of_export_operation()
    {
        // Create test data
        $language = Language::factory()->create(['code' => 'en']);
        
        // Create a large number of translations
        Translation::factory()->count(200)->create([
            'language_id' => $language->id
        ]);

        // Measure time for export operation
        $startTime = microtime(true);
        
        $response = $this->getJson("/api/export/language/en");
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Assert response
        $response->assertStatus(200);
        
        // Performance assertion - should be under 1 second
        $this->assertLessThan(1.0, $executionTime, "Export operation took too long: {$executionTime} seconds");
        
        // Log the performance result
        $this->addToAssertionCount(1);
        echo "\nExport operation execution time: {$executionTime} seconds";
    }

    /** @test */
    public function it_verifies_caching_improves_performance()
    {
        // Create test data
        $language = Language::factory()->create(['code' => 'en']);
        
        // Create translations
        Translation::factory()->count(100)->create([
            'language_id' => $language->id
        ]);

        // First request - should be uncached
        $startTime1 = microtime(true);
        $this->getJson("/api/export/language/en");
        $endTime1 = microtime(true);
        $firstExecutionTime = $endTime1 - $startTime1;

        // Second request - should use cache
        $startTime2 = microtime(true);
        $this->getJson("/api/export/language/en");
        $endTime2 = microtime(true);
        $secondExecutionTime = $endTime2 - $startTime2;

        // The second (cached) request should be significantly faster
        $this->assertLessThan($firstExecutionTime, $secondExecutionTime, 
            "Cached request should be faster than uncached request");
        
        // Log the performance results
        $this->addToAssertionCount(1);
        echo "\nUncached execution time: {$firstExecutionTime} seconds";
        echo "\nCached execution time: {$secondExecutionTime} seconds";
        echo "\nPerformance improvement: " . round(($firstExecutionTime - $secondExecutionTime) / $firstExecutionTime * 100) . "%";
    }

    /** @test */
    public function it_measures_performance_of_translation_creation()
    {
        // Create prerequisites
        $language = Language::factory()->create();
        $tag = Tag::factory()->create();

        $data = [
            'language_id' => $language->id,
            'key' => 'performance.test.key',
            'content' => 'Performance test content',
            'tags' => [$tag->id]
        ];

        // Measure time for creating a translation
        $startTime = microtime(true);
        
        $response = $this->postJson('/api/translations', $data);
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Assert response
        $response->assertStatus(201);
        
        // Performance assertion - should be under 0.5 seconds
        $this->assertLessThan(0.5, $executionTime, "Translation creation took too long: {$executionTime} seconds");
        
        // Log the performance result
        $this->addToAssertionCount(1);
        echo "\nTranslation creation execution time: {$executionTime} seconds";
    }
} 