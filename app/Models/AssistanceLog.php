<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssistanceLog extends Model
{
    /** @use HasFactory<\Database\Factories\AssistanceLogFactory> */
    use HasFactory;

    protected $fillable = [
        'beneficiary_id',
        'assistance_type',
        'description',
        'amount',
        'assisted_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assisted_at' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
