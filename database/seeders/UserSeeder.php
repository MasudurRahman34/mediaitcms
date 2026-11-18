<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Media Admin', 'email' => 'admin@mediaitsoft.co.uk', 'role' => Role::SUPER_ADMIN],
            ['name' => 'Amina Rahman', 'email' => 'admin.team@mediaitsoft.co.uk', 'role' => Role::ADMIN],
            ['name' => 'James Whitfield', 'email' => 'editor@mediaitsoft.co.uk', 'role' => Role::EDITOR],
            ['name' => 'Priya Nair', 'email' => 'recruiter@mediaitsoft.co.uk', 'role' => Role::RECRUITER],
        ];

        foreach ($accounts as $account) {
            User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => 'Password!234',
                    'role_id' => Role::query()->where('slug', $account['role'])->value('id'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
