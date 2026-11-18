<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /** @var array<string, string> */
    public const PERMISSIONS = [
        'pages.manage' => 'Manage pages',
        'content.manage' => 'Manage services & solutions',
        'articles.manage' => 'Manage articles',
        'careers.manage' => 'Manage jobs',
        'applications.view' => 'View applications',
        'media.manage' => 'Manage media',
        'navigation.manage' => 'Manage navigation',
        'settings.manage' => 'Manage settings',
        'users.manage' => 'Manage users & roles',
        'enquiries.view' => 'View enquiries',
        'audit.view' => 'View audit log',
    ];

    public function run(): void
    {
        $permissions = [];

        foreach (self::PERMISSIONS as $slug => $name) {
            $permissions[$slug] = Permission::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'group' => $this->groupFor($slug)],
            );
        }

        $editor = [
            'pages.manage', 'content.manage', 'articles.manage', 'media.manage', 'enquiries.view',
        ];

        $recruiter = ['careers.manage', 'applications.view', 'media.manage'];

        $admin = array_values(array_filter(
            array_keys(self::PERMISSIONS),
            fn (string $slug) => ! in_array($slug, ['users.manage', 'audit.view'], true),
        ));

        $roles = [
            Role::SUPER_ADMIN => [
                'name' => 'Super Admin',
                'description' => 'Unrestricted access to every area of the CMS.',
                'permissions' => array_keys(self::PERMISSIONS),
            ],
            Role::ADMIN => [
                'name' => 'Admin',
                'description' => 'Full content and recruitment access, without user management.',
                'permissions' => $admin,
            ],
            Role::EDITOR => [
                'name' => 'Editor',
                'description' => 'Manages pages, services, solutions, articles and media.',
                'permissions' => $editor,
            ],
            Role::RECRUITER => [
                'name' => 'Recruiter',
                'description' => 'Manages vacancies and candidate applications.',
                'permissions' => $recruiter,
            ],
        ];

        foreach ($roles as $slug => $definition) {
            $role = Role::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'is_system' => true,
                ],
            );

            $role->permissions()->sync(
                collect($definition['permissions'])->map(fn ($permission) => $permissions[$permission]->id)->all(),
            );
        }
    }

    private function groupFor(string $slug): string
    {
        return match (true) {
            str_starts_with($slug, 'pages') => 'Content',
            str_starts_with($slug, 'articles') => 'Content',
            str_starts_with($slug, 'content') => 'Content',
            str_starts_with($slug, 'media') => 'Media',
            str_starts_with($slug, 'careers'), str_starts_with($slug, 'applications') => 'Recruitment',
            str_starts_with($slug, 'enquiries') => 'Inbox',
            default => 'System',
        };
    }
}
