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

        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->uuid("public_id")->unique();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->foreignId("transaction_type_id")->constrained()->cascadeOnDelete();
            $table->foreignId("pet_status_id")->default(1)->constrained();
            $table->foreignId("pet_species_id")->constrained()->cascadeOnDelete();
            $table->foreignId("pet_breed_id")->nullable()->constrained();
            $table->string("color")->nullable();
            $table->string("gender")->nullable();
            $table->string("age")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
