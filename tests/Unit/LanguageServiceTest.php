<?php

namespace Tests\Unit;

use App\Models\Language;
use App\Services\LanguageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class LanguageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LanguageService $languageService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->languageService = new LanguageService();
    }

    /** @test */
    public function it_can_get_all_languages_without_pagination()
    {
        // Create test data
        Language::factory()->count(3)->create();
        
        // Get all languages without pagination
        $result = $this->languageService->getAllLanguages();
        
        // Assert correct data was returned
        $this->assertCount(3, $result);
        $this->assertInstanceOf(Language::class, $result->first());
    }

    /** @test */
    public function it_can_get_all_languages_with_pagination()
    {
        // Create test data
        Language::factory()->count(10)->create();
        
        // Get all languages with pagination
        $result = $this->languageService->getAllLanguages(true, 5);
        
        // Assert correct data was returned
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(5, $result->perPage());
        $this->assertEquals(10, $result->total());
    }

    /** @test */
    public function it_can_create_language()
    {
        // Create a language
        $data = [
            'name' => 'English',
            'code' => 'en',
            'is_active' => true
        ];
        
        $language = $this->languageService->createLanguage($data);
        
        // Assert language was created
        $this->assertInstanceOf(Language::class, $language);
        $this->assertEquals('English', $language->name);
        $this->assertEquals('en', $language->code);
        $this->assertTrue($language->is_active);
        
        $this->assertDatabaseHas('languages', [
            'name' => 'English',
            'code' => 'en',
            'is_active' => true
        ]);
    }

    /** @test */
    public function it_can_update_language()
    {
        // Create a language
        $language = Language::factory()->create([
            'name' => 'Old Name',
            'code' => 'old',
            'is_active' => false
        ]);
        
        // Update the language
        $data = [
            'name' => 'New Name',
            'code' => 'new',
            'is_active' => true
        ];
        
        $updatedLanguage = $this->languageService->updateLanguage($language, $data);
        
        // Assert language was updated
        $this->assertEquals('New Name', $updatedLanguage->name);
        $this->assertEquals('new', $updatedLanguage->code);
        $this->assertTrue($updatedLanguage->is_active);
        
        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'name' => 'New Name',
            'code' => 'new',
            'is_active' => true
        ]);
        
        $this->assertDatabaseMissing('languages', [
            'name' => 'Old Name',
            'code' => 'old'
        ]);
    }

    /** @test */
    public function it_can_delete_language()
    {
        // Create a language
        $language = Language::factory()->create([
            'name' => 'To Delete',
            'code' => 'del'
        ]);
        
        // Delete the language
        $result = $this->languageService->deleteLanguage($language);
        
        // Assert language was deleted
        $this->assertTrue($result);
        $this->assertDatabaseMissing('languages', ['id' => $language->id]);
    }

    /** @test */
    public function it_can_get_active_languages()
    {
        // Create test data
        Language::factory()->create([
            'name' => 'Active 1',
            'code' => 'act1',
            'is_active' => true
        ]);
        
        Language::factory()->create([
            'name' => 'Active 2',
            'code' => 'act2',
            'is_active' => true
        ]);
        
        Language::factory()->create([
            'name' => 'Inactive',
            'code' => 'inact',
            'is_active' => false
        ]);
        
        // Get active languages
        $result = $this->languageService->getActiveLanguages();
        
        // Assert only active languages were returned
        $this->assertCount(2, $result);
        foreach ($result as $language) {
            $this->assertTrue($language->is_active);
        }
    }

    /** @test */
    public function it_can_get_language_by_code()
    {
        // Create test data
        $language = Language::factory()->create([
            'name' => 'English',
            'code' => 'en'
        ]);
        
        // Get language by code
        $result = $this->languageService->getLanguageByCode('en');
        
        // Assert correct language was returned
        $this->assertInstanceOf(Language::class, $result);
        $this->assertEquals($language->id, $result->id);
        $this->assertEquals('English', $result->name);
        $this->assertEquals('en', $result->code);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_language_code()
    {
        // Get language by non-existent code
        $result = $this->languageService->getLanguageByCode('nonexistent');
        
        // Assert null was returned
        $this->assertNull($result);
    }
} 