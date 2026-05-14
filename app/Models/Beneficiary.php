<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Beneficiary extends Model
{
    /** @use HasFactory<\Database\Factories\BeneficiaryFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PENDING = 'pending';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'beneficiary_number',
        'qr_token',
        'full_name',
        'address',
        'barangay',
        'birthdate',
        'gender',
        'contact_number',
        'civil_status',
        'valid_id_path',
        'photo_path',
        'category',
        'status',
        'notes',
        'date_issued',
        'created_by',
        'archived_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'date_issued' => 'date',
            'archived_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Beneficiary $beneficiary): void {
            $beneficiary->beneficiary_number ??= self::generateBeneficiaryNumber();
            $beneficiary->qr_token ??= (string) Str::uuid();
            $beneficiary->date_issued ??= now()->toDateString();
        });
    }

    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_PENDING,
            self::STATUS_INACTIVE,
        ];
    }

    /**
     * @return list<string>
     */
    public static function genders(): array
    {
        return ['Male', 'Female', 'Other'];
    }

    /**
     * @return list<string>
     */
    public static function civilStatuses(): array
    {
        return ['Single', 'Married', 'Separated', 'Widowed'];
    }

    /**
     * @return list<string>
     */
    public static function categories(): array
    {
        return [
            'Senior Citizen',
            'PWD',
            'Solo Parent',
            'Indigent',
            'Youth',
            'General Assistance',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function ageGroups(): array
    {
        return [
            'minor' => '0-17',
            'young_adult' => '18-29',
            'adult' => '30-59',
            'senior' => '60+',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assistanceLogs(): HasMany
    {
        return $this->hasMany(AssistanceLog::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(BeneficiaryDocument::class);
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        $query
            ->when(($filters['archived'] ?? null) === 'only', fn (Builder $builder) => $builder->whereNotNull('archived_at'))
            ->when(! in_array($filters['archived'] ?? null, ['only', 'with'], true), fn (Builder $builder) => $builder->whereNull('archived_at'))
            ->when(filled($filters['search'] ?? null), function (Builder $builder) use ($filters): void {
                $search = trim((string) $filters['search']);
                $builder->where(function (Builder $nested) use ($search): void {
                    $nested
                        ->where('beneficiary_number', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('barangay', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%");
                });
            })
            ->when(filled($filters['barangay'] ?? null), fn (Builder $builder) => $builder->where('barangay', $filters['barangay']))
            ->when(filled($filters['status'] ?? null), fn (Builder $builder) => $builder->where('status', $filters['status']))
            ->when(filled($filters['gender'] ?? null), fn (Builder $builder) => $builder->where('gender', $filters['gender']))
            ->when(filled($filters['category'] ?? null), fn (Builder $builder) => $builder->where('category', $filters['category']))
            ->when(filled($filters['registered_from'] ?? null), fn (Builder $builder) => $builder->whereDate('created_at', '>=', $filters['registered_from']))
            ->when(filled($filters['registered_to'] ?? null), fn (Builder $builder) => $builder->whereDate('created_at', '<=', $filters['registered_to']))
            ->when(filled($filters['age_group'] ?? null), fn (Builder $builder) => $this->applyAgeGroupFilter($builder, (string) $filters['age_group']));

        return match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->oldest(),
            'alphabetical' => $query->orderBy('full_name'),
            'barangay' => $query->orderBy('barangay')->orderBy('full_name'),
            default => $query->latest(),
        };
    }

    public function age(): ?int
    {
        return $this->birthdate?->age;
    }

    private function applyAgeGroupFilter(Builder $query, string $ageGroup): Builder
    {
        $today = Carbon::today();

        return match ($ageGroup) {
            'minor' => $query->whereDate('birthdate', '>', $today->copy()->subYears(18)),
            'young_adult' => $query->whereBetween('birthdate', [$today->copy()->subYears(29), $today->copy()->subYears(18)]),
            'adult' => $query->whereBetween('birthdate', [$today->copy()->subYears(59), $today->copy()->subYears(30)]),
            'senior' => $query->whereDate('birthdate', '<=', $today->copy()->subYears(60)),
            default => $query,
        };
    }

    private static function generateBeneficiaryNumber(): string
    {
        do {
            $number = 'GBMS-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (self::query()->where('beneficiary_number', $number)->exists());

        return $number;
    }
}
