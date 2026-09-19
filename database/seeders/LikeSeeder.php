<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LikeSeeder extends Seeder
{
    private const CHUNK_SIZE = 1000;

    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->all();
        $userPool = array_flip($userIds); // for fast array_rand-by-key sampling
        $now = now();
        $rows = [];
        $total = 0;

        DB::table('posts')->select('id')->orderBy('id')->chunk(500, function ($posts) use ($userPool, $now, &$rows, &$total) {
            foreach ($posts as $post) {
                // 0-25 likers per post, never more than the number of users
                $likerCount = random_int(0, 25);
                if ($likerCount === 0) {
                    continue;
                }

                // sampling distinct keys guarantees no duplicate user_id for this post
                $likers = (array) array_rand($userPool, $likerCount);

                foreach ($likers as $userId) {
                    $rows[] = [
                        'post_id'    => $post->id,
                        'user_id'    => $userId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $total++;
                }

                if (count($rows) >= self::CHUNK_SIZE) {
                    DB::table('likes')->insert($rows);
                    $rows = [];
                }
            }
        });

        if (!empty($rows)) {
            DB::table('likes')->insert($rows);
        }

        $this->command->info('Likes seeded: ' . $total);
    }
}
