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
        Schema::create('gangguans', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_gangguan')->nullable();
            $table->string('lokasi_opd');
            $table->string('jenis_gangguan');
            $table->date('mulai_pengerjaan')->nullable();
            $table->date('selesai_pengerjaan')->nullable();
            $table->text('kendala')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->string('tim_bertugas')->nullable();
            $table->string('status')->default('BELUM DIKERJAKAN');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gangguans');
    }
};
