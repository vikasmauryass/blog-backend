<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as FakerFactory;

class SeoMetadataSeeder extends Seeder
{
    private const CHUNK_SIZE = 500;

    public function run(): void
    {
        $faker = FakerFactory::create();
        $now = now();
        $rows = [];
        $total = 0;

        $posts = DB::table('posts')
            ->join('categories', 'posts.category_id', '=', 'categories.id')
            ->join('subcategories', 'posts.subcategory_id', '=', 'subcategories.id')
            ->select('posts.id', 'posts.title', 'posts.slug', 'posts.excerpt', 'categories.name as category_name', 'subcategories.name as subcategory_name')
            ->orderBy('posts.id');

        $posts->chunk(500, function ($chunk) use ($faker, $now, &$rows, &$total) {
            foreach ($chunk as $post) {
                $keywords = collect([
                    Str::slug($post->category_name, ' '),
                    Str::slug($post->subcategory_name, ' '),
                    Str::slug($post->title, ' '),
                ])->implode(', ');

                $photoId = MediaSeeder::PHOTO_IDS[array_rand(MediaSeeder::PHOTO_IDS)];

                $rows[] = [
                    'post_id'          => $post->id,
                    'meta_title'       => Str::limit($post->title, 60, ''),
                    'meta_description' => $post->excerpt ?: $faker->sentence(20),
                    'meta_keywords'    => $keywords,
                    'canonical_url'    => 'https://example.com/blog/' . $post->slug,
                    'og_title'         => $post->title,
                    'og_description'   => Str::limit($post->excerpt ?: $faker->sentence(20), 160, ''),
                    'og_image'         => "https://picsum.photos/id/{$photoId}/1200/630",
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
                $total++;

                if (count($rows) >= self::CHUNK_SIZE) {
                    DB::table('seo_metadata')->insert($rows);
                    $rows = [];
                }
            }
        });

        if (!empty($rows)) {
            DB::table('seo_metadata')->insert($rows);
        }

        $this->command->info('seo_metadata seeded: ' . $total);
    }
}
