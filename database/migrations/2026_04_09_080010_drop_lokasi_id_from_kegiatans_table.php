<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('kegiatans', 'lokasi_id')) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys=OFF;');

                Schema::create('kegiatans_temp', function (Blueprint $table) {
                    $table->id();
                    $table->string('nama');
                    $table->text('deskripsi')->nullable();
                    $table->date('tanggal')->nullable();
                    $table->time('waktu')->nullable();
                    $table->string('status')->default('rencana');
                    $table->timestamps();
                });

                DB::statement('INSERT INTO kegiatans_temp (id, nama, deskripsi, tanggal, waktu, status, created_at, updated_at)
                    SELECT id, nama, deskripsi, tanggal, waktu, status, created_at, updated_at FROM kegiatans');

                Schema::drop('kegiatans');
                Schema::rename('kegiatans_temp', 'kegiatans');

                DB::statement('PRAGMA foreign_keys=ON;');
            } else {
                Schema::table('kegiatans', function (Blueprint $table) {
                    $table->dropConstrainedForeignId('lokasi_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatans', function (Blueprint $table) {
            $table->unsignedBigInteger('lokasi_id')->nullable();
        });
    }
};
