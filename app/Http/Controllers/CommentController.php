<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;

class CommentController extends Controller
{
    public function index(Post $post)
    {
        $comments = $post->comments()
            ->whereNull('parent_id')
            ->where('status', 'approved')
            ->with([
                'user:id,name',
                'replies' => function ($query) {
                    $query
                        ->where('status', 'approved')
                        ->with('user:id,name')
                        ->latest();
                },
            ])
            ->latest()
            ->paginate(10);

        return CommentResource::collection($comments);
    }

    public function store(
        StoreCommentRequest $request,
        Post $post
    ) {
        if ($post->status !== 'published') {
            abort(404);
        }

        if ($request->parent_id) {

            $parentComment = Comment::query()
                ->where('id', $request->parent_id)
                ->where('post_id', $post->id)
                ->where('status', 'approved')
                ->first();

            abort_unless(
                $parentComment,
                422,
                'Invalid parent comment.'
            );
        }

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
            'status' => 'approved',
        ]);

        $comment->load('user:id,name');

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }
}
