<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photoproduit extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'photoproduits';

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom_du_fichier',
        'produit_id'
    ];

    /**
     * Le chemin de stockage des images
     */
    const STORAGE_PATH = 'default/images';

    /**
     * Relation avec le produit
     */
    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }

    /**
     * Récupère l'URL complète de la photo
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . self::STORAGE_PATH . '/' . $this->nom_du_fichier);
    }

    /**
     * Récupère le chemin complet du fichier
     */
    public function getPathAttribute(): string
    {
        return storage_path('app/public/' . self::STORAGE_PATH . '/' . $this->nom_du_fichier);
    }
}