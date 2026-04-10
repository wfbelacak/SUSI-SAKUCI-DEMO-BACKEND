<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admin extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'admin';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'id_admin';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama_admin',
        'username',
        'password',
        'posisi',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the aspirasi handled by the admin.
     */
    public function aspirasi(): HasMany
    {
        return $this->hasMany(Aspirasi::class, 'id_admin', 'id_admin');
    }
}
