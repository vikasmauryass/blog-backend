<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as FakerFactory;

class CommentSeeder extends Seeder
{
    // weighted: mostly approved
    private const STATUSES = ['approved', 'approved', 'approved', 'approved', 'pending', 'spam'];

    private const CHUNK_SIZE = 500;

    public function run(): void
    {
        $faker = FakerFactory::create();
        $userIds = DB::table('users')->pluck('id')->all();
        $now = now();

        // ---- Phase 1: top-level comments (0-8 per post) ----
        $rows = [];
        $totalTopLevel = 0;

        DB::table('posts')->select('id')->orderBy('id')->chunk(500, function ($posts) use ($faker, $userIds, $now, &$rows, &$totalTopLevel) {
            foreach ($posts as $post) {
                $count = random_int(0, 8);
                for ($c = 0; $c < $count; $c++) {
                    $rows[] = [
                        'post_id'    => $post->id,
                        'user_id'    => $faker->randomElement($userIds),
                        'parent_id'  => null,
                        'content'    => $faker->paragraph(random_int(1, 3)),
                        'status'     => $faker->randomElement(self::STATUSES),
                        'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                        'updated_at' => $now,
                    ];
                    $totalTopLevel++;

                    if (count($rows) >= self::CHUNK_SIZE) {
                        DB::table('comments')->insert($rows);
                        $rows = [];
                    }
                }
            }
        });

        if (!empty($rows)) {
            DB::table('comments')->insert($rows);
            $rows = [];
        }

        // ---- Phase 2: replies (0-3 per existing comment) referencing a parent on the SAME post ----
        $totalReplies = 0;

        DB::table('comments')
            ->select('id', 'post_id')
            ->orderBy('id')
            ->chunk(500, function ($comments) use ($faker, $userIds, $now, &$rows, &$totalReplies) {
                foreach ($comments as $comment) {
                    // not every comment gets replies
                    if (!$faker->boolean(35)) {
                        continue;
                    }

                    $replyCount = random_int(1, 3);
                    for ($r = 0; $r < $replyCount; $r++) {
                        $rows[] = [
                            'post_id'    => $comment->post_id,
                            'user_id'    => $faker->randomElement($userIds),
                            'parent_id'  => $comment->id,
                            'content'    => $faker->sentence(random_int(6, 20)),
                            'status'     => $faker->randomElement(self::STATUSES),
                            'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                            'updated_at' => $now,
                        ];
                        $totalReplies++;

                        if (count($rows) >= self::CHUNK_SIZE) {
                            DB::table('comments')->insert($rows);
                            $rows = [];
                        }
                    }
                }
            });

        if (!empty($rows)) {
            DB::table('comments')->insert($rows);
        }

        $this->command->info("Comments seeded: {$totalTopLevel} top-level + {$totalReplies} replies");
    }
}
