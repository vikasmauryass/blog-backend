<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Post;

class LikeController extends Controller
{
    public function index(Request $request, Post $post)
    {
        $liked = false;

        if ($request->user('api')) {
            $liked = $post->likes()
                ->where('user_id', $request->user('api')->id)
                ->exists();
        }

        return response()->json([
            'data' => [
                'post_id' => $post->id,
                'likes_count' => $post->likes()->count(),
                'liked' => $liked,
            ],
        ]);
    }

    public function store(Request $request, Post $post)
    {
        $like = Like::firstOrCreate([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Post liked successfully.',
            'data' => [
                'post_id' => $post->id,
                'liked' => true,
            ],
        ], 201);
    }

    public function destroy(Request $request, Post $post)
    {
        Like::where('post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json([
            'message' => 'Post unliked successfully.',
            'data' => [
                'post_id' => $post->id,
                'liked' => false,
            ],
        ]);
    }
}
