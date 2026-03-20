<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['role_name' => 'admin'],
            ['role_name' => 'manager'],
            ['role_name' => 'staff'],
            ['role_name' => 'trainer'],
            ['role_name' => 'member'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['role_name' => $role['role_name']]);
        }
    }
}
