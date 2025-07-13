<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class TravelRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'applicant_name',
        'destination',
        'departure_date',
        'return_date',
        'status',
        'reason',
        'cancellation_reason',
        'approved_at',
        'cancelled_at'
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public const STATUS_APPROVED = 'approved';
    public const STATUS_CANCELLED = 'cancelled';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByStatus(Builder $query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange(Builder $query, string $startDate, string $endDate)
    {
        return $query->whereBetween('departure_date', [$startDate, $endDate]);
    }

    public function scopeByDestination(Builder $query, string $destination)
    {
        return $query->where('destination', 'like', "%{$destination}%");
    }

    public function canBeCancelled(): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }

        return Carbon::parse($this->departure_date)->subDay() > Carbon::now();
    }
}
