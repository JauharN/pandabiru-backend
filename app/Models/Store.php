<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'code',
        'name',
        'address',
        'owner',
        'phone',
        'open_hour',
        'close_hour',
        'image_url',
    ];

    /**
     * Accessor untuk URL gambar lengkap
     */
    public function getImageUrlAttribute($value)
    {
        if (!$value) {
            return null;
        }

        // Jika sudah full URL, return as is
        if (str_starts_with($value, 'http')) {
            return $value;
        }

        // Jika path relatif, tambahkan base URL
        return url($value);
    }

    /**
     * Relasi ke Reports (opsional, untuk query)
     */
    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
