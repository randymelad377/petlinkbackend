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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid("public_id")->unique();
            $table->foreignId("user_role_id")->default(1)->constrained()->cascadeOnDelete();
            $table->foreignId("user_status_id")->default(1)->constrained()->cascadeOnDelete();
            $table->string("firstName");
            $table->string("middleName");
            $table->string("lastName");
            $table->string("gender");
            $table->string("age");
            $table->string("houseNumber");
            $table->string("street");
            $table->string("barangay");
            $table->string("municipality")->default("Tuguegarao City");
            $table->string("contactNumber");
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('user_img_path')->nullable()->constrained('user_images')->nullOnDelete();
            $table->boolean("showInfo")->default(false);
            $table->integer("warningCount")->default(0);
            $table->date("suspend_at")->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
