<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Curated, realistic tag pool. Real blogs reuse a fixed set of tags
     * across thousands of posts rather than having one tag per post —
     * add more strings here if you want a bigger pool.
     */
    public const TAGS = [
        // Tech
        'javascript', 'python', 'react', 'nodejs', 'ai', 'machine-learning', 'docker', 'kubernetes',
        'cybersecurity', 'privacy', 'blockchain', 'cloud-computing', 'api', 'database', 'laravel',
        'vuejs', 'devops', 'ci-cd', 'ios', 'android', 'flutter', 'saas', 'open-source', 'automation',
        // Health
        'nutrition-tips', 'mental-health', 'weight-loss', 'yoga', 'meditation', 'wellness', 'fitness',
        'workout', 'diet', 'superfoods', 'sleep', 'stress-management', 'healthy-recipes', 'keto-diet',
        // Finance
        'budgeting', 'investing', 'stocks', 'crypto', 'bitcoin', 'passive-income', 'retirement',
        'taxes', 'insurance', 'side-hustle', 'personal-finance',
        // Travel
        'travel-guide', 'backpacking', 'solo-travel', 'road-trip', 'budget-travel', 'luxury-travel',
        'family-travel', 'digital-nomad', 'visa-guide', 'flight-hacks', 'hotel-review',
        // Food
        'recipes', 'vegan', 'baking-tips', 'world-cuisine', 'street-food', 'restaurant-guide',
        'meal-prep', 'gluten-free',
        // Sports & entertainment
        'football', 'basketball', 'cricket', 'olympics', 'tennis', 'movies-review', 'tv-series',
        'music-news', 'celebrity', 'gaming-news', 'esports', 'podcast', 'book-review',
        // Education & career
        'online-courses', 'career-tips', 'study-hacks', 'productivity', 'remote-work', 'freelancing',
        'interview-tips', 'resume-tips', 'leadership', 'language-learning', 'university-life',
        // Business & marketing
        'startups', 'marketing-tips', 'entrepreneurship', 'ecommerce', 'seo', 'digital-marketing',
        'social-media', 'branding', 'content-writing', 'email-marketing',
        // Lifestyle
        'home-decor', 'parenting-tips', 'self-improvement', 'relationships', 'mindfulness',
        'family', 'diy', 'gardening', 'pets', 'photography',
        // Science
        'space', 'climate-change', 'biology', 'physics', 'innovations', 'environment',
        // Real estate & auto
        'real-estate-tips', 'home-buying', 'car-review', 'electric-vehicles', 'motorcycles',
        // Fashion
        'skincare', 'makeup', 'fashion-trends', 'sustainable-fashion', 'style-guide',
        // Generic/meta tags every blog has
        'tutorial', 'guide', 'tips-and-tricks', 'beginners-guide', 'how-to', 'review', 'comparison',
        'top-10', 'trends-2026', 'inspiration', 'motivation', 'news', 'opinion', 'case-study',
        'checklist', 'faq', 'best-practices', 'mistakes-to-avoid', 'trends', 'deep-dive',
    ];

    public function run(): void
    {
        $now = now();
        $rows = [];

        // array_unique + array_values just in case of accidental duplicates above
        foreach (array_values(array_unique(self::TAGS)) as $name) {
            $rows[] = [
                'name'       => ucwords(str_replace('-', ' ', $name)),
                'slug'       => Str::slug($name),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('tags')->insert($rows);

        $this->command->info('Tags seeded: ' . count($rows));
    }
}
