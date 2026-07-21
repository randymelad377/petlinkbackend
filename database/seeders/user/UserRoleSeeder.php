<?php

namespace Database\Seeders\user;

use App\Models\users\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $userRoles = [
            ["role" => "user"],
            ["role" => "admin"],
            ["role" => "super_admin"]
        ];

        foreach ($userRoles as $role) {
            UserRole::updateOrCreate(
                ["role" => $role["role"]]
            );
        }
    }
}
