<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'pesanan_id',
        'metode_pembayaran',
        'bukti_transfer',
        'tanggal_bayar',
    ];

    /**
     * Tabel asli hanya punya `tanggal_bayar`, tanpa created_at/updated_at.
     */
    public $timestamps = false;

    protected $casts = [
        'tanggal_bayar' => 'datetime',
    ];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }
}
