<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    public const TYPES = ['preventive', 'corrective'];
    public const STATUTS = ['ouvert', 'en_cours', 'clos'];

    protected $fillable = [
        'actif_id', 'type', 'date_demande', 'description',
        'cout', 'statut', 'clos_par', 'date_cloture', 'demande_par',
    ];

    protected function casts(): array
    {
        return [
            'date_demande' => 'date',
            'date_cloture' => 'date',
            'cout' => 'decimal:2',
        ];
    }
}
