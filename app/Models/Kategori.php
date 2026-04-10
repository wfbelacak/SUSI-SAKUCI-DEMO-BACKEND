<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'kategori';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'id_kategori';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ket_kategori',
    ];

    /**
     * Get the input aspirasi for the kategori.
     */
    public function inputAspirasi(): HasMany
    {
        return $this->hasMany(InputAspirasi::class, 'id_kategori', 'id_kategori');
    }

    /**
     * Get the aspirasi for the kategori.
     */
    public function aspirasi(): HasMany
    {
        return $this->hasMany(Aspirasi::class, 'id_kategori', 'id_kategori');
    }
}
