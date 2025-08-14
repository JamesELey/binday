<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'active',
        'assigned_area_ids',
        'phone',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'assigned_area_ids' => 'array',
        'active' => 'boolean',
    ];

    // Role constants
    const ROLE_ADMIN = 'admin';
    const ROLE_WORKER = 'worker';
    const ROLE_CUSTOMER = 'customer';

    /**
     * Role checking methods
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isWorker(): bool
    {
        return $this->role === self::ROLE_WORKER;
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    /**
     * Check if user can manage a specific area
     */
    public function canManageArea(int $areaId): bool
    {
        if ($this->isAdmin()) {
            return true; // Admins can manage all areas
        }

        if ($this->isWorker()) {
            return in_array($areaId, $this->assigned_area_ids ?? []);
        }

        return false; // Customers cannot manage areas
    }

    /**
     * Get the areas this user can manage
     */
    public function getManageableAreaIds(): array
    {
        if ($this->isAdmin()) {
            // Admin can manage all areas - get all area IDs from JSON file
            $areasData = $this->getAllAreas();
            return array_column($areasData, 'id');
        }

        if ($this->isWorker()) {
            return $this->assigned_area_ids ?? [];
        }

        return []; // Customers cannot manage areas
    }

    /**
     * Check if user can edit a specific collection
     */
    public function canEditCollection(array $collection): bool
    {
        if ($this->isAdmin()) {
            return true; // Admins can edit all collections
        }

        if ($this->isWorker()) {
            // Workers can edit collections in their assigned areas
            $collectionAreaId = $this->getAreaIdForAddress($collection['address']);
            return $this->canManageArea($collectionAreaId);
        }

        if ($this->isCustomer()) {
            // Customers can only edit their own collections
            return isset($collection['customer_email']) && 
                   $collection['customer_email'] === $this->email;
        }

        return false;
    }

    /**
     * Get all areas from JSON file
     */
    private function getAllAreas(): array
    {
        $storagePath = storage_path('app/allowed_areas.json');
        
        if (!file_exists($storagePath)) {
            return [];
        }
        
        $data = file_get_contents($storagePath);
        return json_decode($data, true) ?: [];
    }

    /**
     * Get area ID for a given address (simplified - would need proper geocoding)
     */
    private function getAreaIdForAddress(string $address): ?int
    {
        // This is a simplified version - in practice you'd geocode the address
        // and check which area polygon contains it
        $areas = $this->getAllAreas();
        
        // For now, just return the first area if any exist
        return !empty($areas) ? $areas[0]['id'] : null;
    }

    /**
     * Set password with automatic hashing
     */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    /**
     * Scope to get users by role
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to get active users only
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
