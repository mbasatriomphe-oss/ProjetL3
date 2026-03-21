<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'description'
    ];

    /**
     * Les attributs qui doivent être cachés pour les tableaux.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Règles de validation pour le modèle
     *
     * @var array
     */
    public static $rules = [
        'nom' => 'required|string|max:255|unique:categories,nom',
        'description' => 'required|string'
    ];

    /**
     * Règles de validation pour la mise à jour
     *
     * @param int $id
     * @return array
     */
    public static function updateRules($id)
    {
        return [
            'nom' => 'sometimes|string|max:255|unique:categories,nom,' . $id,
            'description' => 'sometimes|string'
        ];
    }
}