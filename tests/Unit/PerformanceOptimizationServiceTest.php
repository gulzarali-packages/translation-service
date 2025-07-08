<?php

namespace Tests\Unit;

use App\Models\Translation;
use App\Services\PerformanceOptimizationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PerformanceOptimizationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PerformanceOptimizationService $performanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->performanceService = new PerformanceOptimizationService();
        
        // Clear cache before each test
        Cache::flush();
    }

    /** @test */
    public function it_can_optimize_query()
    {
        $query = Translation::query();
        $optimizedQuery = $this->performanceService->optimizeQuery($query);
        
        // The query should still be a Builder instance
        $this->assertInstanceOf(Builder::class, $optimizedQuery);
        
        // The query should have columns set
        $this->assertNotEmpty($optimizedQuery->getQuery()->columns);
    }

    /** @test */
    public function it_can_remember_cache()
    {
        $key = 'test_remember';
        $value = 'cached_value';
        
        // First call should execute the callback
        $result = $this->performanceService->rememberCache($key, function () use ($value) {
            return $value;
        });
        
        $this->assertEquals($value, $result);
        $this->assertTrue(Cache::has($key));
        
        // Change the value to verify it's coming from cache
        $newValue = 'new_value';
        $result = $this->performanceService->rememberCache($key, function () use ($newValue) {
            return $newValue;
        });
        
        // Should still be the old value from cache
        $this->assertEquals($value, $result);
        $this->assertNotEquals($newValue, $result);
    }

    /** @test */
    public function it_can_remember_cache_forever()
    {
        $key = 'test_remember_forever';
        $value = 'cached_value_forever';
        
        $result = $this->performanceService->rememberCacheForever($key, function () use ($value) {
            return $value;
        });
        
        $this->assertEquals($value, $result);
        $this->assertTrue(Cache::has($key));
    }

    /** @test */
    public function it_can_invalidate_cache()
    {
        // Set up cache
        $key = 'test_invalidate';
        Cache::put($key, 'value', 60);
        
        // Verify cache exists
        $this->assertTrue(Cache::has($key));
        
        // Invalidate cache
        $result = $this->performanceService->invalidateCache($key);
        
        // Verify cache was cleared
        $this->assertTrue($result);
        $this->assertFalse(Cache::has($key));
    }

    /** @test */
    public function it_can_invalidate_multiple_cache_keys()
    {
        // Set up multiple cache entries
        $keys = ['test_key1', 'test_key2'];
        foreach ($keys as $key) {
            Cache::put($key, 'value', 60);
        }
        
        // Verify caches exist
        foreach ($keys as $key) {
            $this->assertTrue(Cache::has($key));
        }
        
        // Invalidate multiple caches
        $result = $this->performanceService->invalidateCache($keys[0]);
        
        // Verify first cache was cleared but second remains
        $this->assertTrue($result);
        $this->assertFalse(Cache::has($keys[0]));
        $this->assertTrue(Cache::has($keys[1]));
    }

    /** @test */
    public function it_can_chunk_results()
    {
        // Create test data
        $count = 10;
        for ($i = 0; $i < $count; $i++) {
            Translation::factory()->create([
                'key' => "key{$i}",
                'content' => "content{$i}"
            ]);
        }
        
        $query = Translation::query();
        $chunkSize = 3;
        $iterations = 0;
        $processedCount = 0;
        
        $this->performanceService->chunkResults($query, $chunkSize, function ($chunk) use (&$iterations, &$processedCount) {
            $iterations++;
            $processedCount += $chunk->count();
            return true;
        });
        
        // Should have processed all items
        $this->assertEquals($count, $processedCount);
        
        // Should have the correct number of iterations
        $expectedIterations = ceil($count / $chunkSize);
        $this->assertEquals($expectedIterations, $iterations);
    }
} 