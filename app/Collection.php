<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Collection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'customer_name',
        'customer_email',
        'phone',
        'address',
        'bin_type',
        'collection_date',
        'collection_time',
        'status',
        'notes',
        'latitude',
        'longitude',
        'user_id',
        'area_id',
        'is_recurring',
        'parent_collection_id',
        'last_generated_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'collection_date' => 'date',
        'collection_time' => 'datetime:H:i',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_recurring' => 'boolean',
        'last_generated_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_COLLECTED = 'collected';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the user who created this collection
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the area this collection belongs to
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Scope to get collections by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get collections by bin type
     */
    public function scopeByBinType($query, string $binType)
    {
        return $query->where('bin_type', $binType);
    }

    /**
     * Scope to get collections for a specific date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('collection_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get collections for current week
     */
    public function scopeCurrentWeek($query)
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        return $query->whereBetween('collection_date', [$startOfWeek, $endOfWeek]);
    }

    /**
     * Scope to get collections for next week
     */
    public function scopeNextWeek($query)
    {
        $startOfNextWeek = Carbon::now()->addWeek()->startOfWeek();
        $endOfNextWeek = Carbon::now()->addWeek()->endOfWeek();
        
        return $query->whereBetween('collection_date', [$startOfNextWeek, $endOfNextWeek]);
    }

    /**
     * Scope to get collections by customer email
     */
    public function scopeByCustomer($query, string $email)
    {
        return $query->where('customer_email', $email);
    }

    /**
     * Check if user can edit this collection
     */
    public function canBeEditedBy(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isWorker()) {
            // Workers can edit collections in their assigned areas
            return $user->canManageArea($this->area_id);
        }

        if ($user->isCustomer()) {
            // Customers can edit their own collections
            return $this->customer_email === $user->email;
        }

        return false;
    }

    /**
     * Get color for bin type (for map display)
     */
    public function getBinTypeColor(): string
    {
        $colors = [
            'Food' => '#28a745',      // Green
            'Recycling' => '#007bff', // Blue
            'Garden' => '#8b4513',    // Brown
            'General' => '#6c757d',   // Gray
        ];

        return $colors[$this->bin_type] ?? '#6c757d';
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClass(): string
    {
        $classes = [
            'pending' => 'badge-warning',
            'confirmed' => 'badge-info',
            'collected' => 'badge-success',
            'cancelled' => 'badge-danger',
        ];

        return $classes[$this->status] ?? 'badge-secondary';
    }

    /**
     * Set collection area based on address
     */
    public function setAreaFromAddress()
    {
        $area = Area::active()->get()->first(function ($area) {
            return $area->containsAddress($this->address);
        });

        if ($area) {
            $this->area_id = $area->id;
        }
    }

    /**
     * Get the parent collection (if this is a recurring collection)
     */
    public function parent()
    {
        return $this->belongsTo(Collection::class, 'parent_collection_id');
    }

    /**
     * Get all child collections generated from this recurring collection
     */
    public function children()
    {
        return $this->hasMany(Collection::class, 'parent_collection_id');
    }

    /**
     * Scope to get only recurring collections
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Scope to get only one-time collections
     */
    public function scopeOneTime($query)
    {
        return $query->where('is_recurring', false);
    }

    /**
     * Check if this collection is part of a recurring series
     */
    public function isPartOfRecurringSeries(): bool
    {
        return $this->is_recurring || $this->parent_collection_id !== null;
    }

    /**
     * Get the next scheduled collection date (2 weeks from this one)
     */
    public function getNextScheduledDate(): ?Carbon
    {
        if (!$this->is_recurring) {
            return null;
        }

        return $this->collection_date->copy()->addWeeks(2);
    }

    /**
     * Boot the model to set area on save
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($collection) {
            // Auto-set area if not already set
            if (!$collection->area_id) {
                $collection->setAreaFromAddress();
            }
        });
    }
}