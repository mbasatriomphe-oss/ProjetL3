<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailApprovisionnement extends Model
{
    use HasFactory;

    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'detail_approvisionnements';

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'approv_id',
        'produit_id',
        'quantite',
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantite' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Les attributs à ajouter aux tableaux du modèle.
     *
     * @var array
     */
    protected $appends = [
        'total_ligne',
    ];

    /**
     * Relation avec l'approvisionnement parent
     */
    public function approvisionnement()
    {
        return $this->belongsTo(Approvisionnement::class, 'approv_id');
    }

    /**
     * Relation avec le produit
     */
    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }

    /**
     * Calculer le total de la ligne
     */
    public function getTotalLigneAttribute()
    {
        return $this->quantite * $this->approvisionnement->prix_achat;
    }

    /**
     * Scope pour un approvisionnement spécifique
     */
    public function scopeParApprovisionnement($query, $approvId)
    {
        return $query->where('approv_id', $approvId);
    }

    /**
     * Scope pour un produit spécifique
     */
    public function scopeParProduit($query, $produitId)
    {
        return $query->where('produit_id', $produitId);
    }
}