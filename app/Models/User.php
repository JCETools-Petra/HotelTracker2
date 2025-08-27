<?php

namespace App\Models;

use App\Notifications\CustomVerifyEmail; // <-- TAMBAHKAN INI
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'property_id', // <-- TAMBAHKAN INI
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * TAMBAHAN: Definisikan relasi "belongsTo" ke model Property.
     * Ini memungkinkan kita untuk mengambil data properti dari user.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function isHousekeeper()
{
    return $this->role === 'hk';
}

    /**
     * Mengirim notifikasi verifikasi email kustom.
     * Override metode bawaan untuk menggunakan template email kita.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }
}

