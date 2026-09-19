<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Real-world blog taxonomy: category => [subcategories].
     * Add/remove entries here to change what gets seeded — no other
     * code needs to change, PostSeeder reads straight from the DB.
     */
    public const TAXONOMY = [
        'Technology' => ['Artificial Intelligence', 'Web Development', 'Mobile Apps', 'Cybersecurity', 'Cloud Computing', 'Gadgets & Reviews'],
        'Health & Wellness' => ['Nutrition', 'Mental Health', 'Fitness & Exercise', 'Diseases & Conditions', "Women's Health", 'Healthy Recipes'],
        'Finance' => ['Personal Finance', 'Investing', 'Cryptocurrency', 'Insurance', 'Taxes', 'Retirement Planning'],
        'Travel' => ['Destinations', 'Travel Tips', 'Budget Travel', 'Adventure Travel', 'Road Trips'],
        'Food & Cooking' => ['Recipes', 'Restaurant Reviews', 'Baking', 'Vegan & Vegetarian', 'World Cuisine'],
        'Sports' => ['Football', 'Basketball', 'Cricket', 'Tennis', 'Olympics'],
        'Entertainment' => ['Movies', 'TV Shows', 'Music', 'Celebrity News', 'Gaming News'],
        'Education' => ['Online Learning', 'Study Tips', 'Career Advice', 'Test Prep', 'Language Learning'],
        'Business' => ['Startups', 'Marketing', 'Entrepreneurship', 'Management', 'E-commerce'],
        'Lifestyle' => ['Home & Garden', 'Relationships', 'Parenting', 'Fashion Trends', 'Self Improvement'],
        'Science' => ['Space & Astronomy', 'Environment & Climate', 'Biology', 'Physics', 'Innovations'],
        'Real Estate' => ['Buying a Home', 'Renting', 'Home Improvement', 'Market Trends'],
        'Automotive' => ['Car Reviews', 'Electric Vehicles', 'Maintenance Tips', 'Motorcycles'],
        'Gaming' => ['PC Gaming', 'Console Gaming', 'Mobile Gaming', 'Esports'],
        'Fashion & Beauty' => ['Skincare', 'Makeup Tutorials', 'Style Guides', 'Sustainable Fashion'],
    ];

    public function run(): void
    {
        $now = now();
        $categoryRows = [];

        foreach (array_keys(self::TAXONOMY) as $name) {
            $categoryRows[] = [
                'name'        => $name,
                'slug'        => Str::slug($name),
                'description' => $name . ' articles, guides and news.',
                'status'      => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }
        DB::table('categories')->insert($categoryRows);

        // Map name -> id for the subcategory pass
        $categoryIds = DB::table('categories')->pluck('id', 'name');

        $subcategoryRows = [];
        foreach (self::TAXONOMY as $categoryName => $subNames) {
            $categoryId = $categoryIds[$categoryName];

            foreach ($subNames as $subName) {
                $subcategoryRows[] = [
                    'category_id' => $categoryId,
                    'name'        => $subName,
                    // slug unique across ALL subcategories, so prefix with category
                    'slug'        => Str::slug($categoryName . '-' . $subName),
                    'description' => $subName . ' content under ' . $categoryName . '.',
                    'status'      => 1,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
        }
        DB::table('subcategories')->insert($subcategoryRows);

        $this->command->info('Categories seeded: ' . count($categoryRows));
        $this->command->info('Subcategories seeded: ' . count($subcategoryRows));
    }
}
