<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TranslationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate a user for protected routes
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    /** @test */
    public function it_can_get_all_translations()
    {
        // Create test data
        $language = Language::factory()->create();
        $translations = Translation::factory()->count(3)->create([
            'language_id' => $language->id
        ]);

        // Make request
        $response = $this->getJson('/api/translations');

        // Assert response
        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'key', 'content', 'language_id', 'created_at', 'updated_at']
            ],
            'links',
            'meta'
        ]);
    }

    /** @test */
    public function it_can_get_a_single_translation()
    {
        // Create test data
        $language = Language::factory()->create();
        $translation = Translation::factory()->create([
            'language_id' => $language->id,
            'key' => 'test.key',
            'content' => 'Test Content'
        ]);

        // Make request
        $response = $this->getJson("/api/translations/{$translation->id}");

        // Assert response
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $translation->id,
                'key' => 'test.key',
                'content' => 'Test Content',
                'language_id' => $language->id
            ]
        ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_translation()
    {
        // Make request with non-existent ID
        $response = $this->getJson('/api/translations/999');

        // Assert response
        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_create_a_translation()
    {
        // Create test data
        $language = Language::factory()->create();
        $tag = Tag::factory()->create();

        $data = [
            'language_id' => $language->id,
            'key' => 'new.key',
            'content' => 'New Content',
            'tags' => [$tag->id]
        ];

        // Make request
        $response = $this->postJson('/api/translations', $data);

        // Assert response
        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'key' => 'new.key',
                'content' => 'New Content',
                'language_id' => $language->id
            ]
        ]);

        // Assert database
        $this->assertDatabaseHas('translations', [
            'key' => 'new.key',
            'content' => 'New Content',
            'language_id' => $language->id
        ]);

        // Assert tag relationship
        $translationId = $response->json('data.id');
        $this->assertDatabaseHas('translation_tag', [
            'translation_id' => $translationId,
            'tag_id' => $tag->id
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_translation()
    {
        // Make request with missing required fields
        $response = $this->postJson('/api/translations', [
            'content' => 'Missing key and language_id'
        ]);

        // Assert response
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['key', 'language_id']);
    }

    /** @test */
    public function it_can_update_a_translation()
    {
        // Create test data
        $language = Language::factory()->create();
        $tag = Tag::factory()->create();
        $newTag = Tag::factory()->create();
        
        $translation = Translation::factory()->create([
            'language_id' => $language->id,
            'key' => 'old.key',
            'content' => 'Old Content'
        ]);
        
        $translation->tags()->attach($tag->id);

        $data = [
            'key' => 'updated.key',
            'content' => 'Updated Content',
            'tags' => [$newTag->id]
        ];

        // Make request
        $response = $this->putJson("/api/translations/{$translation->id}", $data);

        // Assert response
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $translation->id,
                'key' => 'updated.key',
                'content' => 'Updated Content'
            ]
        ]);

        // Assert database
        $this->assertDatabaseHas('translations', [
            'id' => $translation->id,
            'key' => 'updated.key',
            'content' => 'Updated Content'
        ]);

        // Assert tag relationship was updated
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
    public function it_can_delete_a_translation()
    {
        // Create test data
        $language = Language::factory()->create();
        $translation = Translation::factory()->create([
            'language_id' => $language->id
        ]);

        // Make request
        $response = $this->deleteJson("/api/translations/{$translation->id}");

        // Assert response
        $response->assertStatus(204);

        // Assert database
        $this->assertDatabaseMissing('translations', [
            'id' => $translation->id
        ]);
    }

    /** @test */
    public function it_can_search_translations()
    {
        // Create test data
        $language = Language::factory()->create(['name' => 'English', 'code' => 'en']);
        $tag = Tag::factory()->create(['name' => 'common']);
        
        // Create a translation that should match the search
        $matchingTranslation = Translation::factory()->create([
            'language_id' => $language->id,
            'key' => 'search.key',
            'content' => 'Searchable content'
        ]);
        $matchingTranslation->tags()->attach($tag->id);
        
        // Create a translation that should not match
        Translation::factory()->create([
            'language_id' => $language->id,
            'key' => 'other.key',
            'content' => 'Other content'
        ]);

        // Make search request
        $response = $this->getJson('/api/translations/search?key=search&content=Searchable');

        // Assert response
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJson([
            'data' => [
                [
                    'id' => $matchingTranslation->id,
                    'key' => 'search.key',
                    'content' => 'Searchable content'
                ]
            ]
        ]);
    }

    /** @test */
    public function unauthenticated_users_cannot_access_protected_endpoints()
    {
        // Clear authenticated user
        auth()->guard('sanctum')->logout();
        
        // Attempt to access protected endpoint
        $response = $this->getJson('/api/translations');
        
        // Assert unauthorized response
        $response->assertStatus(401);
    }
} 