<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSubcategoryRequest;
use App\Http\Requests\UpdateSubcategoryRequest;
use App\Http\Resources\SubcategoryResource;
use App\Models\Category;
use App\Models\Subcategory;

class SubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $subcategories = Subcategory::query()
            ->with([
                'category:id,name,slug'
            ])
            ->select([
                'id',
                'category_id',
                'name',
                'slug',
                'description',
                'status',
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
            ->when(
                $request->category_id,
                function ($query, $categoryId) {
                    $query->where(
                        'category_id',
                        $categoryId
                    );
                }
            )
            ->orderBy('name')
            ->paginate(10);

        return SubcategoryResource::collection(
            $subcategories
        );
    }

    public function show(Subcategory $subcategory)
    {
        $subcategory->load(
            'category:id,name,slug'
        );

        return new SubcategoryResource(
            $subcategory
        );
    }

    public function store(StoreSubcategoryRequest $request)
    {
        $subcategory = Subcategory::create(
            $request->validated()
        );

        $subcategory->load(
            'category:id,name,slug'
        );

        return (new SubcategoryResource($subcategory))
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdateSubcategoryRequest $request,
        Subcategory $subcategory
    ) {
        $subcategory->update(
            $request->validated()
        );

        $subcategory->load(
            'category:id,name,slug'
        );

        return new SubcategoryResource(
            $subcategory
        );
    }

    public function destroy(Subcategory $subcategory)
    {
        $subcategory->delete();

        return response()->json([
            'message' => 'Subcategory deleted successfully.',
        ]);
    }
}
