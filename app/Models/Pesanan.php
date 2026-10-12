<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanan';

    protected $fillable = [
        'user_id',
        'nama',
        'tanggal_jadi',
        'jam_pengambilan',
        'alamat',
        'latitude',
        'longitude',
        'total',
        'status',
    ];

    /**
     * Tabel asli hanya punya `created_at`, tidak ada `updated_at`.
     */
    public const UPDATED_AT = null;

    protected $casts = [
        'total'        => 'integer',
        'tanggal_jadi' => 'date',
        'created_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detail()
    {
        return $this->hasMany(DetailPesanan::class, 'pesanan_id');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'pesanan_id');
    }
}
