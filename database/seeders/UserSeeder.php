<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as FakerFactory;

class UserSeeder extends Seeder
{
    // ---- Bump this number to generate more/fewer users ----
    public const TOTAL_USERS = 10000;

    private const CHUNK_SIZE = 500;

    public function run(): void
    {
        $faker = FakerFactory::create();

        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        $userRoleId  = DB::table('roles')->where('name', 'user')->value('id');

        // Hashing bcrypt 10,000 times individually is slow (~minutes).
        // Every seeded user shares the same dummy password on purpose;
        // this is throwaway seed data, not real accounts.
        $sharedPassword = Hash::make('password');

        $now = now();
        $rows = [];

        for ($i = 1; $i <= self::TOTAL_USERS; $i++) {
            // ~2% admins, rest regular users
            $roleId = ($i % 50 === 0) ? $adminRoleId : $userRoleId;

            $rows[] = [
                'role_id'           => $roleId,
                'name'              => $faker->name(),
                'email'             => $faker->unique()->safeEmail(),
                'email_verified_at' => $faker->boolean(85) ? $now : null,
                'password'          => $sharedPassword,
                'status'            => $faker->boolean(95) ? 1 : 0,
                'remember_token'    => null,
                'created_at'        => $faker->dateTimeBetween('-2 years', 'now'),
                'updated_at'        => $now,
            ];

            if (count($rows) >= self::CHUNK_SIZE) {
                DB::table('users')->insert($rows);
                $rows = [];
            }
        }

        if (!empty($rows)) {
            DB::table('users')->insert($rows);
        }

        $this->command->info('Users seeded: ' . self::TOTAL_USERS);
    }
}
