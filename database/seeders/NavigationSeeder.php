<?php

namespace Database\Seeders;

use App\Models\NavigationMenu;
use App\Models\Page;
use App\Models\Service;
use Illuminate\Database\Seeder;

class NavigationSeeder extends Seeder
{
    public function run(): void
    {
        $serviceUrl = fn (string $title) => ($slug = Service::query()
            ->where('title', $title)
            ->value('slug')) ? '/services/'.$slug : '/services';

        $menus = [
            'header' => [
                'name' => 'Header',
                'items' => [
                    ['label' => 'Home', 'page_slug' => 'home'],
                    ['label' => 'About', 'page_slug' => 'about'],
                    ['label' => 'Services', 'url' => '/services'],
                    ['label' => 'Our Work', 'url' => '/solutions'],
                    ['label' => 'News', 'url' => '/news'],
                    ['label' => 'Careers', 'url' => '/careers'],
                    ['label' => 'Contact', 'url' => '/contact'],
                ],
            ],
            'footer_company' => [
                'name' => 'Footer — Company',
                'items' => [
                    ['label' => 'About us', 'page_slug' => 'about'],
                    ['label' => 'Our work', 'url' => '/solutions'],
                    ['label' => 'Careers', 'url' => '/careers'],
                    ['label' => 'Contact', 'url' => '/contact'],
                ],
            ],
            'footer_services' => [
                'name' => 'Footer — Services',
                'items' => [
                    ['label' => 'All services', 'url' => '/services'],
                    ['label' => 'Custom software', 'url' => $serviceUrl('Custom Software Development')],
                    ['label' => 'AI solutions', 'url' => $serviceUrl('AI & Automation Solutions')],
                    ['label' => 'Cloud & DevOps', 'url' => $serviceUrl('Cloud & DevOps Engineering')],
                    ['label' => 'IT support', 'url' => $serviceUrl('IT Support & Managed Services')],
                ],
            ],
            'footer_legal' => [
                'name' => 'Footer — Legal',
                'items' => [
                    ['label' => 'Privacy policy', 'url' => '/privacy-policy'],
                    ['label' => 'Terms of service', 'url' => '/terms-of-service'],
                    ['label' => 'News', 'url' => '/news'],
                ],
            ],
        ];

        foreach ($menus as $key => $definition) {
            $menu = NavigationMenu::query()->updateOrCreate(
                ['key' => $key],
                ['name' => $definition['name']],
            );

            $menu->items()->delete();

            foreach ($definition['items'] as $index => $item) {
                $pageId = isset($item['page_slug'])
                    ? Page::query()->where('slug', $item['page_slug'])->value('id')
                    : null;

                $menu->items()->create([
                    'label' => $item['label'],
                    'url' => $item['url'] ?? null,
                    'page_id' => $pageId,
                    'sort_order' => $index,
                    'is_active' => true,
                ]);
            }
        }
    }
}
