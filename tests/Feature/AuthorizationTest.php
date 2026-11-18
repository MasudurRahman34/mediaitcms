<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    public function test_editor_can_manage_pages_but_not_settings(): void
    {
        $editor = $this->userWithRole(Role::EDITOR);
        $this->grant($editor, ['pages.manage', 'content.manage', 'articles.manage', 'media.manage']);

        $this->actingAs($editor)->get('/admin/api/pages')->assertOk();
        $this->actingAs($editor)->get('/admin/api/settings')->assertForbidden();
        $this->actingAs($editor)->get('/admin/api/users')->assertForbidden();
    }

    public function test_recruiter_can_manage_jobs_but_not_articles(): void
    {
        $recruiter = $this->userWithRole(Role::RECRUITER);
        $this->grant($recruiter, ['careers.manage', 'applications.view']);

        $this->actingAs($recruiter)->get('/admin/api/jobs')->assertOk();
        $this->actingAs($recruiter)->get('/admin/api/applications')->assertOk();
        $this->actingAs($recruiter)->get('/admin/api/articles')->assertForbidden();
    }

    public function test_super_admin_has_access_to_everything(): void
    {
        $admin = $this->admin();

        foreach (['pages', 'services', 'solutions', 'articles', 'jobs', 'applications', 'media', 'settings', 'users', 'enquiries'] as $resource) {
            $this->actingAs($admin)->get('/admin/api/'.$resource)->assertOk();
        }
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function grant(User $user, array $permissions): void
    {
        $ids = Permission::query()
            ->whereIn('slug', $permissions)
            ->pluck('id');

        foreach ($permissions as $slug) {
            Permission::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $slug, 'group' => 'test'],
            );
        }

        $user->role->permissions()->sync(
            Permission::query()->whereIn('slug', $permissions)->pluck('id'),
        );

        $user->load('role.permissions');
    }
}
