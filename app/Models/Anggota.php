<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    protected $fillable = [
        'user_id',
        'nama',
        'telepon',
        'email',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
