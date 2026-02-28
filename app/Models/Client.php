<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'nom',
        'telephone',
        'email',
        'adresse',
    ];

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }
}
