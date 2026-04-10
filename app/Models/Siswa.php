<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'siswa';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'nis';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nis',
        'nama',
        'password',
        'kelas',
        'is_active',
        'dibuat_pada',
        'terakhir_update',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the input aspirasi for the siswa.
     */
    public function inputAspirasi(): HasMany
    {
        return $this->hasMany(InputAspirasi::class, 'nis', 'nis');
    }
}
