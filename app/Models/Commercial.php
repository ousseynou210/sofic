<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commercial extends Model
{
    protected $table = 'commerciaux';

    protected $fillable = [
        'nom',
        'telephone',
        'email',
    ];

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }
}
