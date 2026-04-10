<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaranPublik extends Model
{
    protected $primaryKey = 'id_saran';
    
    protected $fillable = [
        'nama_pengirim',
        'email',
        'no_telepon',
        'kategori_pengirim',
        'id_kategori',
        'isi_saran',
        'status',
    ];

    // Relationship to Kategori
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }
}
