<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('gangguans')) {
            return;
        }

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE gangguans MODIFY tanggal_gangguan DATETIME NULL');
            DB::statement('ALTER TABLE gangguans MODIFY mulai_pengerjaan DATETIME NULL');
            DB::statement('ALTER TABLE gangguans MODIFY selesai_pengerjaan DATETIME NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE gangguans ALTER COLUMN tanggal_gangguan TYPE TIMESTAMP(0) WITHOUT TIME ZONE USING tanggal_gangguan::timestamp');
            DB::statement('ALTER TABLE gangguans ALTER COLUMN mulai_pengerjaan TYPE TIMESTAMP(0) WITHOUT TIME ZONE USING mulai_pengerjaan::timestamp');
            DB::statement('ALTER TABLE gangguans ALTER COLUMN selesai_pengerjaan TYPE TIMESTAMP(0) WITHOUT TIME ZONE USING selesai_pengerjaan::timestamp');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('gangguans')) {
            return;
        }

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE gangguans MODIFY tanggal_gangguan DATE NULL');
            DB::statement('ALTER TABLE gangguans MODIFY mulai_pengerjaan DATE NULL');
            DB::statement('ALTER TABLE gangguans MODIFY selesai_pengerjaan DATE NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE gangguans ALTER COLUMN tanggal_gangguan TYPE DATE USING tanggal_gangguan::date');
            DB::statement('ALTER TABLE gangguans ALTER COLUMN mulai_pengerjaan TYPE DATE USING mulai_pengerjaan::date');
            DB::statement('ALTER TABLE gangguans ALTER COLUMN selesai_pengerjaan TYPE DATE USING selesai_pengerjaan::date');
        }
    }
};
