<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointVente extends Model
{
    protected $table = 'points_vente';

    protected $fillable = [
        'nom',
        'adresse',
        'telephone',
    ];

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }
}
