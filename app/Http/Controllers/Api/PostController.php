<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Support\Facades\DB;


class PostController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'subcategory_id' => [
                'nullable',
                'integer',
                'exists:subcategories,id',
            ],
            'tag_id' => [
                'nullable',
                'integer',
                'exists:tags,id',
            ],
            'sort' => [
                'nullable',
                'in:latest,oldest',
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:50',
            ],
        ]);

        $perPage = $validated['per_page'] ?? 9;

        $posts = Post::query()
            ->select([
                'id',
                'user_id',
                'category_id',
                'subcategory_id',
                'title',
                'slug',
                'excerpt',
                'content_type',
                'status',
                'published_at',
                'created_at',
            ])

            ->with([
                'user:id,name',
                'category:id,name,slug',
                'subcategory:id,name,slug',
                'tags:id,name,slug',
                'media',
            ])

            ->where('status', 'published')

            ->when(
                $validated['search'] ?? null,
                function ($query, $search) {
                    $query->where(function ($query) use ($search) {
                        $query->where(
                            'title',
                            'like',
                            "%{$search}%"
                        )->orWhere(
                            'excerpt',
                            'like',
                            "%{$search}%"
                        );
                    });
                }
            )

            ->when(
                $validated['category_id'] ?? null,
                function ($query, $categoryId) {
                    $query->where(
                        'category_id',
                        $categoryId
                    );
                }
            )

            ->when(
                $validated['subcategory_id'] ?? null,
                function ($query, $subcategoryId) {
                    $query->where(
                        'subcategory_id',
                        $subcategoryId
                    );
                }
            )

            ->when(
                $validated['tag_id'] ?? null,
                function ($query, $tagId) {
                    $query->whereHas(
                        'tags',
                        function ($query) use ($tagId) {
                            $query->where(
                                'tags.id',
                                $tagId
                            );
                        }
                    );
                }
            )

            ->when(
                ($validated['sort'] ?? 'latest') === 'oldest',
                fn($query) =>
                $query->orderBy('published_at', 'asc'),

                fn($query) =>
                $query->orderBy('published_at', 'desc')
            )

            ->when(
                $validated['search'] ?? null,
                function ($query, $search) {
                    $query->where(function ($query) use ($search) {
                        // Split search string into words for better multi-word matching
                        $words = explode(' ', trim($search));

                        foreach ($words as $word) {
                            if (empty($word)) continue;

                            $query->where(function ($subQuery) use ($word) {
                                $subQuery->where('title', 'like', "%{$word}%")
                                    ->orWhere('excerpt', 'like', "%{$word}%")
                                    ->orWhere('slug', 'like', "%{$word}%");
                            });
                        }
                    });
                }
            )

            ->paginate($perPage);

        return PostResource::collection($posts);
    }


    public function show(Post $post)
    {
        abort_if(
            $post->status !== 'published',
            404
        );

        $post->load([
            'user:id,name',
            'category:id,name,slug',
            'subcategory:id,name,slug',
            'tags:id,name,slug',
            'media',
            'seoMetadata',
        ]);

        return new PostResource($post);
    }


    public function store(StorePostRequest $request)
    {
        $data = $request->validated();

        $tagIds = $data['tags'] ?? [];

        unset($data['tags']);

        $data['user_id'] = $request->user()->id;

        $post = Post::create($data);

        $post->tags()->sync($tagIds);

        $post->load([
            'user:id,name',
            'category:id,name,slug',
            'subcategory:id,name,slug',
            'tags:id,name,slug',
        ]);

        return (new PostResource($post))
            ->response()
            ->setStatusCode(201);
    }


    public function update(
        UpdatePostRequest $request,
        Post $post
    ) {
        $data = $request->validated();

        $tagIds = $data['tags'] ?? [];

        unset($data['tags']);

        $post->update($data);

        $post->tags()->sync($tagIds);

        $post->load([
            'user:id,name',
            'category:id,name,slug',
            'subcategory:id,name,slug',
            'tags:id,name,slug',
        ]);

        return new PostResource($post);
    }


    public function destroy(Post $post)
    {
        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully.',
        ]);
    }

    public function adminShow(Post $post)
    {
        $post->load([
            'author',
            'category',
            'subcategory',
            'tags',
            'media',
            'seo',
        ]);

        return response()->json([
            'data' => $post,
        ]);
    }

    public function anythingsearch(Request $request)
    {
        $validated = $request->validate([
            'search'            => ['nullable', 'string', 'max:150'],

            'category_id'       => ['nullable', 'integer', 'exists:categories,id'],
            'category_slug'     => ['nullable', 'string', 'exists:categories,slug'],

            'subcategory_id'    => ['nullable', 'integer', 'exists:subcategories,id'],
            'subcategory_slug'  => ['nullable', 'string', 'exists:subcategories,slug'],

            'tag_id'            => ['nullable', 'integer', 'exists:tags,id'],
            'tag_slug'          => ['nullable', 'string', 'exists:tags,slug'],

            'content_type'      => ['nullable', 'string', 'max:50'],
            'sort'              => ['nullable', 'in:latest,oldest'],
            'per_page'          => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 12;
        $search  = trim($validated['search'] ?? '');

        $posts = Post::query()
            ->select([
                'id',
                'user_id',
                'category_id',
                'subcategory_id',
                'title',
                'slug',
                'excerpt',
                'content',
                'content_type',
                'status',
                'published_at',
                'created_at',
            ])
            ->with([
                'user:id,name',
                'category:id,name,slug',
                'subcategory:id,name,slug',
                'tags:id,name,slug',
                'media',
            ])
            ->where('status', 'published')

            // ---- free-text search across post fields, category, subcategory, tags ----
            ->when($search !== '', function ($query) use ($search) {
                $words = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);
                // also try the words joined with a hyphen, so "career advice"
                // matches a slug stored as "career-advice"
                $hyphenated = implode('-', $words);

                $query->where(function ($outer) use ($search, $words, $hyphenated) {
                    $outer->where('posts.title', 'like', "%{$search}%")
                        ->orWhere('posts.slug', 'like', "%{$search}%")
                        ->orWhere('posts.slug', 'like', "%{$hyphenated}%")
                        ->orWhere('posts.excerpt', 'like', "%{$search}%")
                        ->orWhere('posts.content', 'like', "%{$search}%")
                        ->orWhere('posts.content_type', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($q) use ($search, $hyphenated) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$hyphenated}%");
                        })
                        ->orWhereHas('subcategory', function ($q) use ($search, $hyphenated) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$hyphenated}%");
                        })
                        ->orWhereHas('tags', function ($q) use ($search, $hyphenated) {
                            $q->where('tags.name', 'like', "%{$search}%")
                                ->orWhere('tags.slug', 'like', "%{$search}%")
                                ->orWhere('tags.slug', 'like', "%{$hyphenated}%");
                        });

                    // word-by-word fallback: handles multi-word phrases where
                    // order or punctuation differs from the stored value
                    if (count($words) > 1) {
                        foreach ($words as $word) {
                            $outer->orWhere('posts.title', 'like', "%{$word}%")
                                ->orWhereHas('category', fn($q) => $q->where('name', 'like', "%{$word}%")->orWhere('slug', 'like', "%{$word}%"))
                                ->orWhereHas('subcategory', fn($q) => $q->where('name', 'like', "%{$word}%")->orWhere('slug', 'like', "%{$word}%"))
                                ->orWhereHas('tags', fn($q) => $q->where('tags.name', 'like', "%{$word}%")->orWhere('tags.slug', 'like', "%{$word}%"));
                        }
                    }
                });
            })

            // ---- slug filters: use these from /category/{slug}, /subcategory/{slug} pages ----
            ->when(
                $validated['category_slug'] ?? null,
                fn($query, $slug) => $query->whereHas('category', fn($c) => $c->where('slug', $slug))
            )
            ->when(
                $validated['subcategory_slug'] ?? null,
                fn($query, $slug) => $query->whereHas('subcategory', fn($c) => $c->where('slug', $slug))
            )
            ->when(
                $validated['tag_slug'] ?? null,
                fn($query, $slug) => $query->whereHas('tags', fn($t) => $t->where('tags.slug', $slug))
            )

            // ---- id filters kept for backward compatibility ----
            ->when(
                $validated['category_id'] ?? null,
                fn($query, $catId) => $query->where('category_id', $catId)
            )
            ->when(
                $validated['subcategory_id'] ?? null,
                fn($query, $subId) => $query->where('subcategory_id', $subId)
            )
            ->when(
                $validated['tag_id'] ?? null,
                fn($query, $tagId) => $query->whereHas('tags', fn($q) => $q->where('tags.id', $tagId))
            )

            ->when(
                $validated['content_type'] ?? null,
                fn($query, $type) => $query->where('content_type', $type)
            )

            ->when(
                ($validated['sort'] ?? 'latest') === 'oldest',
                fn($query) => $query->orderBy('published_at', 'asc'),
                fn($query) => $query->orderBy('published_at', 'desc')
            )

            ->paginate($perPage)
            ->withQueryString();

        return PostResource::collection($posts);
    }
}
