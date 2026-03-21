<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'clients';

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'adresse',
        'numero_tel',
        'user_id',
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Les attributs à ajouter aux tableaux du modèle.
     *
     * @var array
     */
    protected $appends = [
        'nom_complet',
        'email',
    ];

    /**
     * Relation avec l'utilisateur (un client appartient à un utilisateur)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Obtenir le nom complet depuis l'utilisateur associé
     */
    public function getNomCompletAttribute()
    {
        return $this->user ? $this->user->nom . ' ' . $this->user->post_nom : null;
    }

    /**
     * Obtenir l'email depuis l'utilisateur associé
     */
    public function getEmailAttribute()
    {
        return $this->user ? $this->user->email : null;
    }

    /**
     * Scope pour rechercher des clients
     */
    public function scopeSearch($query, $term)
    {
        return $query->whereHas('user', function ($q) use ($term) {
            $q->where('nom', 'LIKE', "%{$term}%")
              ->orWhere('post_nom', 'LIKE', "%{$term}%")
              ->orWhere('email', 'LIKE', "%{$term}%");
        })->orWhere('adresse', 'LIKE', "%{$term}%")
          ->orWhere('numero_tel', 'LIKE', "%{$term}%");
    }
}