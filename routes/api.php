<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SubcategoryController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\SeoMetadataController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\LikeController;


// for Public Route 

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get(
    '/categories/{category}/subcategories',
    [CategoryController::class, 'subcategories']
);
Route::get(
    '/subcategories',
    [SubcategoryController::class, 'index']
);

Route::get(
    '/subcategories/{subcategory}',
    [SubcategoryController::class, 'show']
);

Route::get(
    '/posts',
    [PostController::class, 'index']
);

Route::get(
    '/posts/search',
    [PostController::class, 'anythingsearch']
);

Route::get(
    '/posts/{post:slug}',
    [PostController::class, 'show']
);

Route::get(
    '/tags',
    [TagController::class, 'index']
);

Route::get(
    '/tags/{tag}',
    [TagController::class, 'show']
);

Route::get(
    '/posts/{post}/seo',
    [SeoMetadataController::class, 'show']
);

Route::get(
    '/posts/{post}/comments',
    [CommentController::class, 'index']
);

Route::get(
    '/posts/{post}/likes',
    [LikeController::class, 'index']
);

Route::get(
    '/posts/{post}/reviews',
    [ReviewController::class, 'index']
);

// for auth route 
Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {

        Route::get('/me', [AuthController::class, 'me']);

        Route::post('/logout', [AuthController::class, 'logout']);

    });
});

Route::middleware('auth:api')->group(function () {
    Route::post(
        '/posts/{post}/comments',
        [CommentController::class, 'store']
    );

    // Route::put(
    //     '/comments/{comment}',
    //     [CommentController::class, 'update']
    // );

    // Route::delete(
    //     '/comments/{comment}',
    //     [CommentController::class, 'destroy']
    // );

    Route::post(
        '/posts/{post}/like',
        [LikeController::class, 'store']
    );

    Route::delete(
        '/posts/{post}/like',
        [LikeController::class, 'destroy']
    );

    Route::post(
        '/posts/{post}/reviews',
        [ReviewController::class, 'store']
    );

    Route::put(
        '/reviews/{review}',
        [ReviewController::class, 'update']
    );

    Route::delete(
        '/reviews/{review}',
        [ReviewController::class, 'destroy']
    );
});

// for admin route

// Route::middleware('auth:api')
//     ->prefix('admin')
//     ->group(function () {

//         Route::post(
//             '/categories',
//             [CategoryController::class, 'store']
//         );

//         Route::put(
//             '/categories/{category}',
//             [CategoryController::class, 'update']
//         );

//         Route::delete(
//             '/categories/{category}',
//             [CategoryController::class, 'destroy']
//         );
//     });

Route::middleware(['auth:api', 'admin'])
    ->prefix('admin')
    ->group(function () {

        Route::post(
            '/categories',
            [CategoryController::class, 'store']
        );

        Route::put(
            '/categories/{category}',
            [CategoryController::class, 'update']
        );

        Route::delete(
            '/categories/{category}',
            [CategoryController::class, 'destroy']
        );

        Route::post(
            '/subcategories',
            [SubcategoryController::class, 'store']
        );

        Route::put(
            '/subcategories/{subcategory}',
            [SubcategoryController::class, 'update']
        );

        Route::delete(
            '/subcategories/{subcategory}',
            [SubcategoryController::class, 'destroy']
        );

        Route::post(
            '/tags',
            [TagController::class, 'store']
        );

        Route::put(
            '/tags/{tag}',
            [TagController::class, 'update']
        );

        Route::delete(
            '/tags/{tag}',
            [TagController::class, 'destroy']
        );

    Route::get('/posts/{post}', [PostController::class, 'show']);
    // Route::get('/posts/{post}', [PostController::class, 'adminShow']);
        Route::post(
            '/posts',
            [PostController::class, 'store']
        );

        Route::put(
            '/posts/{post}',
            [PostController::class, 'update']
        );

        Route::delete(
            '/posts/{post}',
            [PostController::class, 'destroy']
        );

        Route::post(
            '/posts/{post}/media',
            [MediaController::class, 'store']
        );

        Route::put(
            '/posts/{post}/seo',
            [SeoMetadataController::class, 'update']
        );
});
