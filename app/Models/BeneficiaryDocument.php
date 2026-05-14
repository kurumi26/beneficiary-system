<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeneficiaryDocument extends Model
{
    /** @use HasFactory<\Database\Factories\BeneficiaryDocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'beneficiary_id',
        'title',
        'file_path',
        'uploaded_by',
    ];

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
