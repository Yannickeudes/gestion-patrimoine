<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Actif extends Model
{
    public const STATUTS = ['en_service', 'maintenance', 'reforme', 'cession', 'sorti'];
    public const ETATS = ['neuf', 'bon', 'use', 'hors_service'];

    protected $fillable = [
        'numero_serie', 'designation', 'categorie_id', 'marque', 'modele',
        'date_acquisition', 'valeur_acquisition', 'valeur_actuelle',
        'etat', 'statut', 'localisation_id', 'responsable_id', 'description',
    ];

    protected function casts(): array
    {
        return [
            'date_acquisition' => 'date',
            'valeur_acquisition' => 'decimal:2',
            'valeur_actuelle' => 'decimal:2',
        ];
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function localisation(): BelongsTo
    {
        return $this->belongsTo(Localisation::class);
    }

    public function affectations(): HasMany
    {
        return $this->hasMany(Affectation::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }
}
