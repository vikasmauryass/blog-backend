<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'alt_text' => [
                'nullable',
                'string',
                'max:255',
            ],
            'caption' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $file = $request->file('file');

        $path = $file->store('media/images', 'public');

        $media = $post->media()->create([
            'type' => 'image',
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'alt_text' => $request->alt_text,
            'caption' => $request->caption,
        ]);

        return response()->json([
            'message' => 'Image uploaded successfully.',
            'data' => $media,
        ], 201);
    }

    public function destroy(Media $media)
    {
        Storage::disk('public')->delete(
            $media->file_path
        );

        $media->delete();

        return response()->json([
            'message' => 'Media deleted successfully.'
        ]);
    }
}
