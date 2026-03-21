<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produit extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'produits';

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'description',
        'prix',
        'categorie_id',
        'unite_id'
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'prix' => 'integer',
        'categorie_id' => 'integer',
        'unite_id' => 'integer'
    ];

    /**
     * Relation avec la catégorie
     */
    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    /**
     * Relation avec l'unité
     * Note: La table s'appelle 'unites' dans la migration
     */
    public function unite(): BelongsTo
    {
        return $this->belongsTo(Unite::class, 'unite_id');
    }

    /**
     * Relation avec les photos du produit
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photoproduit::class, 'produit_id');
    }

    /**
     * Récupère la première photo du produit (photo principale)
     */
    public function getPremierePhotoAttribute()
    {
        return $this->photos()->first();
    }

    /**
     * Récupère l'URL complète d'une photo
     */
    public function getPhotoUrlAttribute($photoName)
    {
        return asset('storage/default/images/' . $photoName);
    }
}