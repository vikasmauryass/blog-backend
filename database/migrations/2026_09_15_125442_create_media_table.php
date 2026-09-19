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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnDelete();

            $table->string('type');

            $table->string('file_path');

            $table->string('file_name')->nullable();

            $table->string('mime_type')->nullable();

            $table->unsignedBigInteger('file_size')->nullable();

            $table->string('alt_text')->nullable();

            $table->string('caption')->nullable();
            $table->timestamps();
            $table->index(['post_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
