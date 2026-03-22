<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Approvisionnement extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'approvisionnements';

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date_approv',
        'quantite',
        'prix_achat',
        'fournisseur_id',
        'admin_id'
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_approv' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relation avec le fournisseur
     */
    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
    }

    /**
     * Relation avec l'administrateur (user)
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Relation avec les détails d'approvisionnement
     */
    public function details(): HasMany
    {
        return $this->hasMany(DetailApprovisionnement::class, 'approv_id');
    }

    /**
     * Relation avec les produits via les détails
     */
    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'detail_approvisionnements', 'approv_id', 'produit_id')
                    ->withPivot('quantite')
                    ->withTimestamps();
    }

    /**
     * Accesseur pour le montant total de l'approvisionnement
     */
    public function getMontantTotalAttribute(): float
    {
        $total = 0;
        foreach ($this->details as $detail) {
            $total += $detail->quantite * $detail->prix_achat;
        }
        return $total;
    }

    /**
     * Accesseur pour la date formatée
     */
    public function getDateFormateeAttribute(): string
    {
        return $this->date_approv->format('d/m/Y H:i');
    }

    /**
     * Scope pour les approvisionnements par fournisseur
     */
    public function scopeByFournisseur($query, $fournisseurId)
    {
        return $query->where('fournisseur_id', $fournisseurId);
    }

    /**
     * Scope pour les approvisionnements par admin
     */
    public function scopeByAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * Scope pour les approvisionnements entre deux dates
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date_approv', [$startDate, $endDate]);
    }
}