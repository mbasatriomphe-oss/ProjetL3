<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailApprovisionnement extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'detail_approvisionnements';

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'approv_id',
        'produit_id',
        'quantite'
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantite' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relation avec l'approvisionnement parent
     */
    public function approvisionnement(): BelongsTo
    {
        return $this->belongsTo(Approvisionnement::class, 'approv_id');
    }

    /**
     * Relation avec le produit
     */
    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }

    /**
     * Accesseur pour le sous-total
     */
    public function getSousTotalAttribute(): float
    {
        return $this->quantite * ($this->approvisionnement->prix_achat ?? 0);
    }
}