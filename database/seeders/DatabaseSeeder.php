<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Full fake-data pipeline for the blog schema.
     *
     * Run with:  php artisan db:seed
     *
     * This TRUNCATES categories, subcategories, tags, posts, post_tags,
     * media, seo_metadata, comments, likes, reviews and users, then
     * rebuilds everything from scratch. Roles are left untouched
     * (RoleSeeder just makes sure admin/user exist).
     */
    public function run(): void
    {
        ini_set('memory_limit', '1024M');

        Schema::disableForeignKeyConstraints();
        DB::table('reviews')->truncate();
        DB::table('likes')->truncate();
        DB::table('comments')->truncate();
        DB::table('seo_metadata')->truncate();
        DB::table('media')->truncate();
        DB::table('post_tags')->truncate();
        DB::table('posts')->truncate();
        DB::table('tags')->truncate();
        DB::table('subcategories')->truncate();
        DB::table('categories')->truncate();
        DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();

        $start = microtime(true);

        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
            PostSeeder::class,
            PostTagSeeder::class,
            MediaSeeder::class,
            SeoMetadataSeeder::class,
            CommentSeeder::class,
            LikeSeeder::class,
            ReviewSeeder::class,
        ]);

        $this->command->info('Done in ' . round(microtime(true) - $start, 2) . 's');
    }
}
