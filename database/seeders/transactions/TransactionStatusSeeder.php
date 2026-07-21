<?php

namespace Database\Seeders\transactions;

use App\Models\forms\TransactionStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $transactionStatus = [
            ["transaction_status" => "onGoing"],
            ["transaction_status" => "cancelled"],
            ["transaction_status" => "completed"]
        ];

        foreach ($transactionStatus as $status) {
            TransactionStatus::updateOrCreate(
                ["transaction_status" => $status["transaction_status"]]
            );
        }
    }
}
