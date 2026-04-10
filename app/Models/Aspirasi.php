<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aspirasi extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'aspirasi';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'id_aspirasi';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id_pelaporan',
        'id_kategori',
        'id_admin',
        'status',
        'feedback',
        'foto_tanggapan',
        'detail_tanggapan',
    ];

    /**
     * Get the input aspirasi that owns the aspirasi.
     */
    public function inputAspirasi(): BelongsTo
    {
        return $this->belongsTo(InputAspirasi::class, 'id_pelaporan', 'id_pelaporan');
    }

    /**
     * Get the kategori that owns the aspirasi.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    /**
     * Get the admin that owns the aspirasi.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }
}
