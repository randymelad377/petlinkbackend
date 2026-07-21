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
        Schema::create('vet_clinics', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 15, 12);
            $table->decimal('longitude', 15, 12);
            $table->boolean("isOpen")->default(true);
            $table->string("clinic_name");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vet_clinics');
    }
};
