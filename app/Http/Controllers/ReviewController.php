<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Review;

class ReviewController extends Controller
{
    public function index(Post $post)
    {
        $reviews = $post->reviews()
            ->with('user:id,name')
            ->latest()
            ->paginate(10);

        $averageRating = $post->reviews()
            ->avg('rating');

        return response()->json([
            'data' => [
                'average_rating' => round($averageRating ?? 0, 1),
                'reviews' => $reviews,
            ],
        ]);
    }

    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],
            'review' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $review = Review::updateOrCreate(
            [
                'post_id' => $post->id,
                'user_id' => $request->user()->id,
            ],
            [
                'rating' => $validated['rating'],
                'review' => $validated['review'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Review submitted successfully.',
            'data' => $review,
        ], 201);
    }

    public function update(
        Request $request,
        Review $review
    ) {
        if ($review->user_id !== $request->user()->id) {
            abort(403, 'You are not allowed to update this review.');
        }

        $validated = $request->validate([
            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],
            'review' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $review->update($validated);

        return response()->json([
            'message' => 'Review updated successfully.',
            'data' => $review->fresh(),
        ]);
    }

    public function destroy(
        Request $request,
        Review $review
    ) {
        if ($review->user_id !== $request->user()->id) {
            abort(403, 'You are not allowed to delete this review.');
        }

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully.',
        ]);
    }
}
