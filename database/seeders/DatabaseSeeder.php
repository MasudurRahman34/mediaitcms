<?php

namespace Database\Seeders;

use App\Services\NavigationRepository;
use App\Services\Seo\SitemapBuilder;
use App\Services\SettingsRepository;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            SettingSeeder::class,
            MediaSeeder::class,
            ServiceSeeder::class,
            SolutionSeeder::class,
            ArticleSeeder::class,
            TeamSeeder::class,
            JobSeeder::class,
            PageSeeder::class,
            NavigationSeeder::class,
            EnquirySeeder::class,
        ]);

        app(SettingsRepository::class)->flush();
        app(NavigationRepository::class)->flush();
        app(SitemapBuilder::class)->flush();
    }
}
