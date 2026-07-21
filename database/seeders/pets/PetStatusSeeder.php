<?php

namespace Database\Seeders\pets;

use App\Models\pet\PetStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PetStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $petStatus = [
            ["status" => "pending"],
            ["status" => "verified"],
            ["status" => "rehome"],
            ["status" => "found"],
            ["status" => "missing"],
            ["status" => "rehomed"],
            ["status" => "retrieved"],
            ["status" => "returned"],
            ["status" => "inTransaction"],
            ["status" => "softDeleted"],
            ["status" => "deleted"],
        ];

        foreach ($petStatus as $status) {
            PetStatus::updateOrCreate(
                ["status" => $status["status"]]
            );
        }
    }
}
