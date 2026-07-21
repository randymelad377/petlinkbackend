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
        Schema::create('missings', function (Blueprint $table) {
            $table->id();
            $table->foreignId("pet_id")->nullable()->constrained("pets")->cascadeOnDelete();
            $table->foreignId("history_id")->nullable()->constrained()->cascadeOnDelete();
            $table->string("description");
            $table->date("date_lost")->nullable();
            $table->string("lost_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('missings');
    }
};
