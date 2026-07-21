<?php

namespace Database\Seeders\transactions;

use App\Models\transactions\TransactionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $transactionType = [
            ["transaction_type" => "rehome"],
            ["transaction_type" => "found"],
            ["transaction_type" => "missing"],
            ["transaction_type" => "none"]
        ];

        foreach ($transactionType as $type) {
            TransactionType::updateOrCreate(
                ["transaction_type" => $type["transaction_type"]]
            );
        }
    }
}
