<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_statuses', function (Blueprint $table) {
            $table->id();
            $table->string("user_status");
            $table->timestamps();
        });

        DB::table("user_statuses")->insert([
            ["user_status" => "active", 'created_at' => now(), 'updated_at' => now(),],
            ["user_status" => "suspend", 'created_at' => now(), 'updated_at' => now(),],
            ["user_status" => "banned", 'created_at' => now(), 'updated_at' => now(),],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_statuses');
    }
};
