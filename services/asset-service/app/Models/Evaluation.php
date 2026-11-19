<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    protected $fillable = [
        'actif_id', 'date_eval', 'valeur_estimee', 'etat', 'observation', 'eval_par',
    ];

    protected function casts(): array
    {
        return [
            'date_eval' => 'date',
            'valeur_estimee' => 'decimal:2',
        ];
    }

    public function actif(): BelongsTo
    {
        return $this->belongsTo(Actif::class);
    }
}
