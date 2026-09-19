<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Requests\UpdateSeoMetadataRequest;
use App\Http\Resources\SeoMetadataResource;

class SeoMetadataController extends Controller
{
    public function show(Post $post)
    {
        $seo = $post->seoMetadata;

        if (!$seo) {
            return response()->json([
                'message' => 'SEO metadata not found.'
            ], 404);
        }

        return new SeoMetadataResource($seo);
    }

    public function update(
        UpdateSeoMetadataRequest $request,
        Post $post
    ) {
        $seo = $post->seoMetadata()->updateOrCreate(
            [
                'post_id' => $post->id,
            ],
            $request->validated()
        );

        return new SeoMetadataResource($seo);
    }
}
