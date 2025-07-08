<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TagService
{
    /**
     * Get all tags with optional pagination.
     *
     * @param bool $paginate
     * @param int $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getAllTags(bool $paginate = false, int $perPage = 15): Collection|LengthAwarePaginator
    {
        return $paginate 
            ? Tag::paginate($perPage) 
            : Tag::all();
    }

    /**
     * Create a new tag.
     *
     * @param array $data
     * @return Tag
     */
    public function createTag(array $data): Tag
    {
        return Tag::create($data);
    }

    /**
     * Update an existing tag.
     *
     * @param Tag $tag
     * @param array $data
     * @return Tag
     */
    public function updateTag(Tag $tag, array $data): Tag
    {
        $tag->update($data);
        return $tag;
    }

    /**
     * Delete a tag.
     *
     * @param Tag $tag
     * @return bool
     */
    public function deleteTag(Tag $tag): bool
    {
        return $tag->delete();
    }

    /**
     * Find tags by names.
     *
     * @param array $names
     * @return Collection
     */
    public function findTagsByNames(array $names): Collection
    {
        return Tag::whereIn('name', $names)->get();
    }

    /**
     * Get tag IDs from tag names.
     *
     * @param array $names
     * @return array
     */
    public function getTagIdsByNames(array $names): array
    {
        return $this->findTagsByNames($names)->pluck('id')->toArray();
    }
} 