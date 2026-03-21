<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approvisionnement extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_approv',
        'quantite',
        'prix_achat',
        'fournisseur_id',
        'admin_id'
    ];

    protected $casts = [
        'date_approv' => 'datetime'
    ];

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function detailApprovisionnements()
    {
        return $this->hasMany(DetailApprovisionnement::class, 'approv_id');
    }
}