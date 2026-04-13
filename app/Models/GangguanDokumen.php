<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GangguanDokumen extends Model
{
    protected $table = 'gangguan_documents';

    protected $fillable = [
        'gangguan_id',
        'user_id',
        'original_name',
        'stored_name',
        'drive_file_id',
        'drive_url',
        'drive_content_url',
        'mime_type',
        'file_size',
        'caption',
    ];

    public function gangguan()
    {
        return $this->belongsTo(Gangguan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
