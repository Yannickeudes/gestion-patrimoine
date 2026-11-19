<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Affectation extends Model
{
    protected $fillable = [
        'actif_id', 'service_destinataire', 'date_debut', 'date_fin', 'observe_par',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
        ];
    }

    public function actif(): BelongsTo
    {
        return $this->belongsTo(Actif::class);
    }
}
