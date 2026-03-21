<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fournisseur extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'fournisseurs';

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'adresse',
        'ville',
        'pays',
        'contact'
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relation avec les approvisionnements (si la table existe)
     * Un fournisseur peut avoir plusieurs approvisionnements
     */
    public function approvisionnements(): HasMany
    {
        return $this->hasMany(Approvisionnement::class, 'fournisseur_id');
    }

    /**
     * Relation avec les produits (via approvisionnements)
     */
    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'approvisionnements', 'fournisseur_id', 'produit_id')
                    ->withPivot('quantite', 'prix_achat', 'date_approvisionnement')
                    ->withTimestamps();
    }

    /**
     * Accesseur pour le nom complet formaté
     */
    public function getNomCompletAttribute(): string
    {
        return "{$this->nom} ({$this->ville}, {$this->pays})";
    }

    /**
     * Accesseur pour l'adresse complète
     */
    public function getAdresseCompleteAttribute(): string
    {
        return "{$this->adresse}, {$this->ville}, {$this->pays}";
    }

    /**
     * Scope pour rechercher par nom
     */
    public function scopeSearchByName($query, $nom)
    {
        return $query->where('nom', 'LIKE', "%{$nom}%");
    }

    /**
     * Scope pour rechercher par ville
     */
    public function scopeSearchByVille($query, $ville)
    {
        return $query->where('ville', 'LIKE', "%{$ville}%");
    }

    /**
     * Scope pour rechercher par pays
     */
    public function scopeSearchByPays($query, $pays)
    {
        return $query->where('pays', 'LIKE', "%{$pays}%");
    }

    /**
     * Scope pour les fournisseurs actifs (si vous avez un champ actif)
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }
}