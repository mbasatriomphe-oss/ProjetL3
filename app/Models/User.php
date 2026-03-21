<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'post_nom',
        'email',
        'password',
        'role',
    ];

    /**
     * Les attributs qui doivent être cachés pour la sérialisation.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relation avec le client (un utilisateur peut avoir un client)
     */
    public function client()
    {
        return $this->hasOne(Client::class, 'user_id');
    }

    /**
     * Vérifier si l'utilisateur est un admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Vérifier si l'utilisateur est un simple utilisateur
     */
    public function isUser()
    {
        return $this->role === 'user';
    }

    /**
     * Vérifier si l'utilisateur a un profil client
     */
    public function hasClient()
    {
        return $this->client()->exists();
    }
}