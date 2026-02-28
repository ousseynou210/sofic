<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    protected $table = 'depenses';

    protected $fillable = [
        'compte_id',
        'date_depense',
        'categorie',
        'fournisseur',
        'description',
        'montant',
        'mode',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'date_depense' => 'date',
            'montant' => 'decimal:2',
        ];
    }

    public function compte(): BelongsTo
    {
        return $this->belongsTo(Compte::class);
    }
}
