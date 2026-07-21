<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\admin\AdminSeeder;
use Database\Seeders\admin\VetClinicSeeder;
use Database\Seeders\pets\PetStatusSeeder;
use Database\Seeders\transactions\TransactionStatusSeeder;
use Database\Seeders\transactions\TransactionTypeSeeder;
use Database\Seeders\user\UserRoleSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PetStatusSeeder::class,
            TransactionTypeSeeder::class,
            UserRoleSeeder::class,
            AdminSeeder::class,
            TransactionStatusSeeder::class,
            VetClinicSeeder::class
        ]);
    }
}
