<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            ['name' => 'Sarah Whitfield', 'role' => 'Managing Director', 'bio' => 'Leads delivery and client strategy. Fifteen years building digital products for regulated industries.'],
            ['name' => 'Amina Rahman', 'role' => 'Head of Engineering', 'bio' => 'Architects and reviews every build. Previously led platform teams at a FTSE 250 retailer.'],
            ['name' => 'Daniel Okoro', 'role' => 'Principal AI Engineer', 'bio' => 'Builds retrieval and evaluation pipelines for production AI. Focused on measurable groundedness.'],
            ['name' => 'Priya Nair', 'role' => 'Product Design Lead', 'bio' => 'Turns complex workflows into interfaces people can use without training.'],
            ['name' => 'James Whitfield', 'role' => 'Cloud & Platform Lead', 'bio' => 'Infrastructure as code, observability and cost engineering across AWS and Azure.'],
            ['name' => 'Elena Duarte', 'role' => 'Senior Software Engineer', 'bio' => 'Full-stack delivery with a focus on data-heavy interfaces and performance.'],
        ];

        $photoKeys = ['hero-team', 'office', 'solution-atlas', 'article-team', 'solution-lexai', 'article-design'];

        foreach ($members as $index => $member) {
            TeamMember::query()->updateOrCreate(
                ['name' => $member['name']],
                [
                    'role_title' => $member['role'],
                    'bio' => $member['bio'],
                    'photo_media_id' => MediaSeeder::id($photoKeys[$index % count($photoKeys)]),
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }
    }
}
