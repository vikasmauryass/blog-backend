<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'title' => $this->title,

            'slug' => $this->slug,

            'excerpt' => $this->excerpt,

            'content' => $this->content,

            'content_type' => $this->content_type,

            'status' => $this->status,

            'published_at' => $this->published_at,

            'author' => $this->whenLoaded(
                'user',
                function () {
                    return [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                    ];
                }
            ),

            'category' => $this->whenLoaded(
                'category',
                function () {
                    return [
                        'id' => $this->category->id,
                        'name' => $this->category->name,
                        'slug' => $this->category->slug,
                    ];
                }
            ),

            'subcategory' => $this->whenLoaded(
                'subcategory',
                function () {
                    return [
                        'id' => $this->subcategory->id,
                        'name' => $this->subcategory->name,
                        'slug' => $this->subcategory->slug,
                    ];
                }
            ),

            'tags' => TagResource::collection(
                $this->whenLoaded('tags')
            ),

            'media' => MediaResource::collection(
                $this->whenLoaded('media')
            ),

            'seo' => new SeoMetadataResource(
                $this->whenLoaded('seoMetadata')
            ),

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,
        ];
    }
}
