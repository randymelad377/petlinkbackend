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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId("sender_id")->constrained("users")->cascadeOnDelete();
            $table->foreignId("conversation_id")->constrained("conversations")->cascadeOnDelete();
            $table->string("message")->nullable();
            $table->boolean("deleted_by_sender")->default(false)->nullable();
            $table->boolean("deleted_by_receiver")->default(false)->nullable();
            $table->string("image_message_path")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
