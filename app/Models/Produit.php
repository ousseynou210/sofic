<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produit extends Model
{
    protected $table = 'produits';

    protected $fillable = [
        'nom',
        'categorie',
        'prix_vente',
        'stock_qty',
    ];

    protected function casts(): array
    {
        return [
            'prix_vente' => 'decimal:2',
            'stock_qty' => 'integer',
        ];
    }

    public function factureLignes(): HasMany
    {
        return $this->hasMany(FactureLigne::class);
    }
}
