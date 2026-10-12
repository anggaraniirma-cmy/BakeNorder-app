<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $primaryKey = 'kunci';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['kunci', 'nilai'];

    /**
     * Ambil nilai pengaturan berdasarkan kunci.
     */
    public static function ambil(string $kunci, $default = null)
    {
        $row = static::find($kunci);

        return $row ? $row->nilai : $default;
    }

    /**
     * Simpan / perbarui nilai pengaturan.
     */
    public static function simpan(string $kunci, $nilai): void
    {
        static::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
    }
}
