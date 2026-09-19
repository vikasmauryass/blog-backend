<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\SubcategoryResource;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $search = trim($validated['search'] ?? '');

        $perPage = $validated['per_page'] ?? 10;

        $categories = Category::query()
            ->select([
                'id',
                'name',
                'slug',
                'description',
                'status',
                'created_at',
            ])
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
                }
            )
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return CategoryResource::collection($categories);
    }


    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category
        
    ) {
        $category->update($request->validated());

        return new CategoryResource(
            $category->fresh()
        );
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }

    public function subcategories(Category $category)
    {
        $subcategories = $category->subcategories()
            ->select([
                'id',
                'category_id',
                'name',
                'slug',
                'status',
            ])
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return SubcategoryResource::collection(
            $subcategories
        );
    }
}
