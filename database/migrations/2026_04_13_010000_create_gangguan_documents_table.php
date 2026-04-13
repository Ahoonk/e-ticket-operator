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
        Schema::create('gangguan_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gangguan_id')->constrained('gangguans')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('original_name');
            $table->string('stored_name')->nullable();
            $table->string('drive_file_id')->unique();
            $table->text('drive_url');
            $table->text('drive_content_url')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('caption')->nullable();
            $table->timestamps();

            $table->index(['gangguan_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gangguan_documents');
    }
};
