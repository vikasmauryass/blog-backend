<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $tags = Tag::query()
            ->select([
                'id',
                'name',
                'slug',
                'created_at',
            ])
            ->when(
                $request->search,
                function ($query, $search) {
                    $query->where(
                        'name',
                        'like',
                        "%{$search}%"
                    );
                }
            )
            ->orderBy('name')
            ->paginate(20);

        return TagResource::collection($tags);
    }

    public function show(Tag $tag)
    {
        return new TagResource($tag);
    }

    public function store(StoreTagRequest $request)
    {
        $tag = Tag::create(
            $request->validated()
        );

        return (new TagResource($tag))
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdateTagRequest $request,
        Tag $tag
    ) {
        $tag->update(
            $request->validated()
        );

        return new TagResource(
            $tag->fresh()
        );
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return response()->json([
            'message' => 'Tag deleted successfully.',
        ]);
    }
}
