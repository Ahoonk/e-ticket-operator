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
        Schema::dropIfExists('anggotas');
        Schema::dropIfExists('lokasis');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('anggotas', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('email')->nullable();
            $table->string('telepon')->nullable();
            $table->string('organisasi')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('lokasis', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('alamat')->nullable();
            $table->string('kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }
};
