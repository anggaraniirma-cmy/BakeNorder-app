<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * Tabel asli tidak punya kolom created_at / updated_at.
     */
    public $timestamps = false;

    /**
     * Kolom tabel asli memakai `nama` (bukan `name`).
     */
    protected $fillable = [
        'nama',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * users.role = 'admin' | 'user'
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'user_id');
    }
}
