<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categorie extends Model
{
    public const TYPES = ['immobilier', 'vehicule', 'informatique', 'mobilier', 'autre'];

    protected $fillable = ['nom', 'type', 'description'];

    public function actifs(): HasMany
    {
        return $this->hasMany(Actif::class);
    }
}
