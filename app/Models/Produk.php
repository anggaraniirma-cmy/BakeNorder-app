<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';

    protected $fillable = [
        'nama_produk',
        'harga',
        'foto',
        'deskripsi',
    ];

    public $timestamps = false;

    protected $casts = [
        'harga'      => 'integer',
        'created_at' => 'datetime',
    ];

    public function detail()
    {
        return $this->hasMany(DetailPesanan::class, 'produk_id');
    }

    /**
     * URL foto produk (file disimpan di public/assets).
     */
    public function getFotoUrlAttribute(): string
    {
        return asset('assets/' . $this->foto);
    }
}
