<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InputAspirasi extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'input_aspirasi';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'id_pelaporan';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = true;
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nis',
        'id_kategori',
        'lokasi',
        'foto_dokumentasi',
        'keterangan',
        'status_review',
    ];

    /**
     * Query scope: pending review.
     */
    public function scopePending($query)
    {
        return $query->where('status_review', 'pending');
    }

    /**
     * Query scope: diterima (accepted).
     */
    public function scopeDiterima($query)
    {
        return $query->where('status_review', 'diterima');
    }

    /**
     * Query scope: ditolak (rejected).
     */
    public function scopeDitolak($query)
    {
        return $query->where('status_review', 'ditolak');
    }

    /**
     * Get the siswa that owns the input aspirasi.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'nis', 'nis');
    }

    /**
     * Get the kategori that owns the input aspirasi.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    /**
     * Get the aspirasi for the input aspirasi.
     */
    public function aspirasi(): HasMany
    {
        return $this->hasMany(Aspirasi::class, 'id_pelaporan', 'id_pelaporan');
    }
}
