<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostTagSeeder extends Seeder
{
    private const CHUNK_SIZE = 1000;

    public function run(): void
    {
        $tagIds = DB::table('tags')->pluck('id')->all();
        $now = now();
        $rows = [];
        $total = 0;

        DB::table('posts')->select('id')->orderBy('id')->chunk(500, function ($posts) use ($tagIds, $now, &$rows, &$total) {
            foreach ($posts as $post) {
                $tagCount = random_int(2, 5);
                // array_rand on the tag pool avoids duplicate tag on the same post
                $chosen = (array) array_rand(array_flip($tagIds), $tagCount);

                foreach ($chosen as $tagId) {
                    $rows[] = [
                        'post_id'    => $post->id,
                        'tag_id'     => $tagId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $total++;
                }

                if (count($rows) >= self::CHUNK_SIZE) {
                    DB::table('post_tags')->insert($rows);
                    $rows = [];
                }
            }
        });

        if (!empty($rows)) {
            DB::table('post_tags')->insert($rows);
        }

        $this->command->info('post_tags seeded: ' . $total);
    }
}
