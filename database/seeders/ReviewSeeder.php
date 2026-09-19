<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as FakerFactory;

class ReviewSeeder extends Seeder
{
    // weighted: mostly approved
    private const STATUSES = ['approved', 'approved', 'approved', 'pending'];

    private const CHUNK_SIZE = 500;

    public function run(): void
    {
        $faker = FakerFactory::create();
        $userIds = DB::table('users')->pluck('id')->all();
        $userPool = array_flip($userIds);
        $now = now();
        $rows = [];
        $total = 0;

        DB::table('posts')->select('id')->orderBy('id')->chunk(500, function ($posts) use ($faker, $userPool, $now, &$rows, &$total) {
            foreach ($posts as $post) {
                // 0-4 reviewers per post
                $reviewerCount = random_int(0, 4);
                if ($reviewerCount === 0) {
                    continue;
                }

                $reviewers = (array) array_rand($userPool, $reviewerCount);

                foreach ($reviewers as $userId) {
                    $rows[] = [
                        'post_id'    => $post->id,
                        'user_id'    => $userId,
                        'rating'     => random_int(1, 5),
                        'review'     => $faker->sentence(random_int(8, 20)),
                        'status'     => $faker->randomElement(self::STATUSES),
                        'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                        'updated_at' => $now,
                    ];
                    $total++;
                }

                if (count($rows) >= self::CHUNK_SIZE) {
                    DB::table('reviews')->insert($rows);
                    $rows = [];
                }
            }
        });

        if (!empty($rows)) {
            DB::table('reviews')->insert($rows);
        }

        $this->command->info('Reviews seeded: ' . $total);
    }
}
