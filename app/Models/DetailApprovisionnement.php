<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailApprovisionnement extends Model
{
    use HasFactory;

    protected $table = 'detail_approvisionnements';

    protected $fillable = [
        'approv_id',
        'produit_id',
        'quantite'
    ];

    public function approvisionnement()
    {
        return $this->belongsTo(Approvisionnement::class, 'approv_id');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }
}