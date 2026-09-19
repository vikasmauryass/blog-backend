<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as FakerFactory;

class MediaSeeder extends Seeder
{
    /**
     * Stable Picsum photo IDs — https://picsum.photos/id/{id}/1200/800 always
     * returns the SAME real photo for a given id, so these are genuine,
     * free-license stock photos, just reused across posts instead of
     * fetching 10,000 unique images.
     */
    public const PHOTO_IDS = [
        10, 20, 30, 40, 60, 100, 110, 120, 130, 140,
        150, 160, 170, 180, 190, 200, 210, 220, 230, 240,
    ];

    private const CHUNK_SIZE = 500;

    public function run(): void
    {
        $faker = FakerFactory::create();
        $now = now();
        $rows = [];
        $total = 0;

        DB::table('posts')->select('id', 'title')->orderBy('id')->chunk(500, function ($posts) use ($faker, $now, &$rows, &$total) {
            foreach ($posts as $post) {
                $mediaCount = random_int(1, 3);

                for ($m = 0; $m < $mediaCount; $m++) {
                    $photoId = self::PHOTO_IDS[array_rand(self::PHOTO_IDS)];
                    $url = "https://picsum.photos/id/{$photoId}/1200/800";

                    $rows[] = [
                        'post_id'    => $post->id,
                        'type'       => 'image',
                        'file_path'  => $url,
                        'file_name'  => "photo-{$photoId}.jpg",
                        'mime_type'  => 'image/jpeg',
                        'file_size'  => random_int(80_000, 500_000),
                        'alt_text'   => $post->title,
                        'caption'    => $faker->sentence(8),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $total++;
                }

                if (count($rows) >= self::CHUNK_SIZE) {
                    DB::table('media')->insert($rows);
                    $rows = [];
                }
            }
        });

        if (!empty($rows)) {
            DB::table('media')->insert($rows);
        }

        $this->command->info('Media rows seeded: ' . $total);
    }
}
