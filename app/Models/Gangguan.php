<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gangguan extends Model
{
    protected $fillable = [
        'tanggal_gangguan',
        'lokasi_opd',
        'jenis_gangguan',
        'mulai_pengerjaan',
        'selesai_pengerjaan',
        'kendala',
        'tindak_lanjut',
        'tim_bertugas',
        'status',
        'keterangan',
    ];
}
