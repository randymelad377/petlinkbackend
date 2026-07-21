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
        Schema::create('pet_previous_owners', function (Blueprint $table) {
            $table->id();
            $table->foreignId("pet_id")->constrained()->cascadeOnDelete();
            $table->foreignId("transaction_type_id")->constrained()->cascadeOnDelete();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->foreignId("current_user_id")->constrained("users")->cascadeOnDelete();
            $table->foreignId("history_id")->nullable()->constrained()->cascadeOnDelete();
            $table->integer("ownership_order");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pet_previous_owners');
    }
};
