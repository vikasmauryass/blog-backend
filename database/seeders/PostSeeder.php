<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as FakerFactory;

class PostSeeder extends Seeder
{
    // ---- Bump this number to generate more/fewer posts ----
    public const TOTAL_POSTS = 10000;

    private const CHUNK_SIZE = 500;

    private const CONTENT_TYPES = ['article', 'video', 'mixed'];

    // weighted: mostly published, some draft/scheduled
    private const STATUSES = ['published', 'published', 'published', 'draft', 'scheduled'];

    private const TITLE_PATTERNS = [
        'The Ultimate Guide to %s',
        '10 Things You Didn\'t Know About %s',
        'How %s Is Changing in 2026',
        'A Beginner\'s Guide to %s',
        'Why %s Matters More Than Ever',
        '%s: Tips, Tricks and Common Mistakes',
        'Everything You Need to Know About %s',
        'The Future of %s',
        '%s Explained Simply',
        'Top Trends in %s This Year',
    ];

    public function run(): void
    {
        $faker = FakerFactory::create();

        $userIds = DB::table('users')->pluck('id')->all();

        // category_id => [subcategory rows]
        $subcategoriesByCategory = DB::table('subcategories')->get()->groupBy('category_id');
        $categoryIds = array_keys($subcategoriesByCategory->all());

        $now = now();
        $rows = [];
        $usedSlugs = [];

        for ($i = 1; $i <= self::TOTAL_POSTS; $i++) {
            $categoryId = $faker->randomElement($categoryIds);
            $subcategory = $faker->randomElement($subcategoriesByCategory[$categoryId]->all());

            $topic = $subcategory->name;
            $title = sprintf($faker->randomElement(self::TITLE_PATTERNS), $topic);

            $slug = Str::slug($title);
            // guarantee uniqueness across 10k+ rows
            if (isset($usedSlugs[$slug])) {
                $slug .= '-' . $i;
            }
            $usedSlugs[$slug] = true;

            $paragraphs = $faker->paragraphs(random_int(4, 8), false);
            $content = '<h2>' . $title . '</h2>' . "\n" .
                collect($paragraphs)->map(fn ($p) => "<p>{$p}</p>")->implode("\n");

            $status = $faker->randomElement(self::STATUSES);
            $createdAt = $faker->dateTimeBetween('-1 year', 'now');
            $publishedAt = in_array($status, ['published', 'scheduled'], true)
                ? $faker->dateTimeBetween($createdAt, '+2 months')
                : null;

            $rows[] = [
                'user_id'        => $faker->randomElement($userIds),
                'category_id'    => $categoryId,
                'subcategory_id' => $subcategory->id,
                'title'          => $title,
                'slug'           => $slug,
                'excerpt'        => $faker->sentence(random_int(15, 25)),
                'content'        => $content,
                'content_type'   => $faker->randomElement(self::CONTENT_TYPES),
                'status'         => $status,
                'published_at'   => $publishedAt,
                'created_at'     => $createdAt,
                'updated_at'     => $now,
            ];

            if (count($rows) >= self::CHUNK_SIZE) {
                DB::table('posts')->insert($rows);
                $rows = [];
            }
        }

        if (!empty($rows)) {
            DB::table('posts')->insert($rows);
        }

        $this->command->info('Posts seeded: ' . self::TOTAL_POSTS);
    }
}
