<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compte extends Model
{
    protected $table = 'comptes';

    protected $fillable = [
        'nom',
        'type',
        'solde_initial',
    ];

    protected function casts(): array
    {
        return [
            'solde_initial' => 'decimal:2',
        ];
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    public function depenses(): HasMany
    {
        return $this->hasMany(Depense::class);
    }
}
