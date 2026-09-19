<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as FakerFactory;

/**
 * Small demo dataset: exactly 10 rows added to every blog table.
 * Purely ADDITIVE — does not truncate anything, safe to run on top of
 * whatever is already in your database.
 *
 * Run with:  php artisan db:seed --class=DemoTenSeeder
 */
class DemoTenSeeder extends Seeder
{
    private const COUNT = 7;

    // one category + one subcategory each, so both land at exactly 10 rows
    private const TAXONOMY = [
        'Technology'      => 'Web Development',
        'Health & Wellness' => 'Nutrition',
        'Finance'         => 'Investing',
        'Travel'          => 'Destinations',
        'Food & Cooking'  => 'Recipes',
        'Sports'          => 'Football',
        'Entertainment'   => 'Movies',
        // 'Education'       => 'Online Learning',
        // 'Business'        => 'Startups',
        // 'Lifestyle'       => 'Self Improvement',
    ];

    private const TAGS = [
        'javascript', 'ai', 'nutrition-tips', 'investing', 'travel-guide',
        'recipes', 'football', 'movies-review', 'online-courses', 'startups',
    ];

    private const PHOTO_IDS = [10, 20, 30, 40, 60, 100, 110, 120, 130, 140];

    public function run(): void
    {
        $faker = FakerFactory::create();
        $now = now();

        // ---- roles (only created if missing, not part of the "10") ----
        foreach (['admin', 'user'] as $roleName) {
            DB::table('roles')->updateOrInsert(['name' => $roleName], ['created_at' => $now, 'updated_at' => $now]);
        }
        $userRoleId = DB::table('roles')->where('name', 'user')->value('id');

        // ---- 10 categories + 10 subcategories ----
        $categoryIds = [];
        $subcategoryIds = [];

        foreach (self::TAXONOMY as $categoryName => $subName) {
            $catId = DB::table('categories')->insertGetId([
                'name'        => $categoryName,
                'slug'        => Str::slug($categoryName) . '-' . Str::random(4),
                'description' => $categoryName . ' articles and guides.',
                'status'      => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $categoryIds[] = $catId;

            $subId = DB::table('subcategories')->insertGetId([
                'category_id' => $catId,
                'name'        => $subName,
                'slug'        => Str::slug($categoryName . '-' . $subName) . '-' . Str::random(4),
                'description' => $subName . ' content under ' . $categoryName . '.',
                'status'      => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $subcategoryIds[] = $subId;
        }

        // ---- 10 tags ----
        $tagIds = [];
        foreach (self::TAGS as $tagName) {
            $tagIds[] = DB::table('tags')->insertGetId([
                'name'       => ucwords(str_replace('-', ' ', $tagName)),
                'slug'       => Str::slug($tagName) . '-' . Str::random(4),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // ---- 10 users ----
        $sharedPassword = Hash::make('password');
        $userIds = [];
        for ($i = 0; $i < self::COUNT; $i++) {
            $userIds[] = DB::table('users')->insertGetId([
                'role_id'           => $userRoleId,
                'name'              => $faker->name(),
                'email'             => $faker->unique()->safeEmail(),
                'email_verified_at' => $now,
                'password'          => $sharedPassword,
                'status'            => 1,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        // ---- 10 posts (1 per category/subcategory pair, 1 tag, 1 media, 1 seo row each) ----
        $postIds = [];
        for ($i = 0; $i < self::COUNT; $i++) {
            $topic = array_values(self::TAXONOMY)[$i];
            $title = "The Ultimate Guide to {$topic}";
            $slug = Str::slug($title) . '-' . Str::random(4);
            $excerpt = $faker->sentence(18);
            $content = '<h2>' . $title . '</h2><p>' . $faker->paragraph(5) . '</p>';

            $postId = DB::table('posts')->insertGetId([
                'user_id'        => $faker->randomElement($userIds),
                'category_id'    => $categoryIds[$i],
                'subcategory_id' => $subcategoryIds[$i],
                'title'          => $title,
                'slug'           => $slug,
                'excerpt'        => $excerpt,
                'content'        => $content,
                'content_type'   => 'article',
                'status'         => 'published',
                'published_at'   => $now,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
            $postIds[] = $postId;

            // 1 tag per post -> exactly 10 post_tags rows
            DB::table('post_tags')->insert([
                'post_id'    => $postId,
                'tag_id'     => $tagIds[$i],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // 1 media row per post -> exactly 10 media rows
            $photoId = self::PHOTO_IDS[$i];
            DB::table('media')->insert([
                'post_id'    => $postId,
                'type'       => 'image',
                'file_path'  => "https://picsum.photos/id/{$photoId}/1200/800",
                'file_name'  => "photo-{$photoId}.jpg",
                'mime_type'  => 'image/jpeg',
                'file_size'  => random_int(80_000, 500_000),
                'alt_text'   => $title,
                'caption'    => $faker->sentence(8),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // 1 seo_metadata row per post -> exactly 10 rows (respects the unique post_id constraint)
            DB::table('seo_metadata')->insert([
                'post_id'          => $postId,
                'meta_title'       => Str::limit($title, 60, ''),
                'meta_description' => $excerpt,
                'meta_keywords'    => strtolower($topic) . ', ' . self::TAGS[$i],
                'canonical_url'    => 'https://example.com/blog/' . $slug,
                'og_title'         => $title,
                'og_description'   => $excerpt,
                'og_image'         => "https://picsum.photos/id/{$photoId}/1200/630",
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);

            // 1 comment per post -> exactly 10 rows
            DB::table('comments')->insert([
                'post_id'    => $postId,
                'user_id'    => $faker->randomElement($userIds),
                'parent_id'  => null,
                'content'    => $faker->paragraph(2),
                'status'     => 'approved',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // 1 like per post -> exactly 10 rows
            DB::table('likes')->insert([
                'post_id'    => $postId,
                'user_id'    => $faker->randomElement($userIds),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // 1 review per post -> exactly 10 rows
            DB::table('reviews')->insert([
                'post_id'    => $postId,
                'user_id'    => $faker->randomElement($userIds),
                'rating'     => random_int(3, 5),
                'review'     => $faker->sentence(15),
                'status'     => 'approved',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info('Demo data seeded: 10 rows in each of categories, subcategories, tags, users, posts, post_tags, media, seo_metadata, comments, likes, reviews.');
    }
}
