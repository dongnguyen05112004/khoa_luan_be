<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'role_name' => 'admin',   'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'role_name' => 'manager',  'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'role_name' => 'staff',    'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'role_name' => 'trainer',  'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'role_name' => 'member',   'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(['id' => $role['id']], $role);
        }
    }
}
