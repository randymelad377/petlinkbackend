<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->uuid("public_id");
            $table->foreignId("transaction_type_id")->constrained()->cascadeOnDelete();
            $table->foreignId("pet_poster_id")->constrained("users")->cascadeOnDelete();
            $table->foreignId("pet_interested_id")->constrained("users")->cascadeOnDelete();
            $table->foreignId("pet_id")->constrained()->cascadeOnDelete();
            $table->date("transaction_started");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
