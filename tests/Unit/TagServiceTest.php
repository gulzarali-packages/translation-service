<?php

namespace Tests\Unit;

use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class TagServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TagService $tagService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagService = new TagService();
    }

    /** @test */
    public function it_can_get_all_tags_without_pagination()
    {
        // Create test data
        Tag::factory()->count(3)->create();
        
        // Get all tags without pagination
        $result = $this->tagService->getAllTags();
        
        // Assert correct data was returned
        $this->assertCount(3, $result);
        $this->assertInstanceOf(Tag::class, $result->first());
    }

    /** @test */
    public function it_can_get_all_tags_with_pagination()
    {
        // Create test data
        Tag::factory()->count(10)->create();
        
        // Get all tags with pagination
        $result = $this->tagService->getAllTags(true, 5);
        
        // Assert correct data was returned
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(5, $result->perPage());
        $this->assertEquals(10, $result->total());
    }

    /** @test */
    public function it_can_create_tag()
    {
        // Create a tag
        $data = ['name' => 'test-tag'];
        $tag = $this->tagService->createTag($data);
        
        // Assert tag was created
        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertEquals('test-tag', $tag->name);
        $this->assertDatabaseHas('tags', ['name' => 'test-tag']);
    }

    /** @test */
    public function it_can_update_tag()
    {
        // Create a tag
        $tag = Tag::factory()->create(['name' => 'old-name']);
        
        // Update the tag
        $data = ['name' => 'new-name'];
        $updatedTag = $this->tagService->updateTag($tag, $data);
        
        // Assert tag was updated
        $this->assertEquals('new-name', $updatedTag->name);
        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => 'new-name']);
        $this->assertDatabaseMissing('tags', ['name' => 'old-name']);
    }

    /** @test */
    public function it_can_delete_tag()
    {
        // Create a tag
        $tag = Tag::factory()->create(['name' => 'to-delete']);
        
        // Delete the tag
        $result = $this->tagService->deleteTag($tag);
        
        // Assert tag was deleted
        $this->assertTrue($result);
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    /** @test */
    public function it_can_find_tags_by_names()
    {
        // Create test data
        $tags = [
            Tag::factory()->create(['name' => 'tag1']),
            Tag::factory()->create(['name' => 'tag2']),
            Tag::factory()->create(['name' => 'tag3'])
        ];
        
        // Find tags by names
        $result = $this->tagService->findTagsByNames(['tag1', 'tag3']);
        
        // Assert correct tags were found
        $this->assertCount(2, $result);
        $this->assertEquals($tags[0]->id, $result->first()->id);
        $this->assertEquals($tags[2]->id, $result->last()->id);
    }

    /** @test */
    public function it_can_get_tag_ids_by_names()
    {
        // Create test data
        $tags = [
            Tag::factory()->create(['name' => 'tag1']),
            Tag::factory()->create(['name' => 'tag2']),
            Tag::factory()->create(['name' => 'tag3'])
        ];
        
        // Get tag IDs by names
        $result = $this->tagService->getTagIdsByNames(['tag1', 'tag3']);
        
        // Assert correct IDs were returned
        $this->assertCount(2, $result);
        $this->assertEquals($tags[0]->id, $result[0]);
        $this->assertEquals($tags[2]->id, $result[1]);
    }
} 