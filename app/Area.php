<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'postcodes',
        'active',
        'type',
        'coordinates',
        'bin_types',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'active' => 'boolean',
        'coordinates' => 'array',
        'bin_types' => 'array',
    ];

    /**
     * Scope to get active areas only
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get areas by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get collections for this area
     */
    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    /**
     * Get users assigned to this area (workers)
     */
    public function assignedWorkers()
    {
        return User::where('role', 'worker')
                   ->whereJsonContains('assigned_area_ids', $this->id)
                   ->get();
    }

    /**
     * Check if an address is within this area
     */
    public function containsAddress(string $address): bool
    {
        if ($this->type === 'postcode') {
            return $this->containsPostcode($address);
        }
        
        // For polygon areas, would need geocoding and point-in-polygon check
        return false;
    }

    /**
     * Check if address postcode is covered by this area
     */
    private function containsPostcode(string $address): bool
    {
        if (empty($this->postcodes)) {
            return false;
        }

        // Extract postcode from address
        $postcode = $this->extractPostcodeFromAddress($address);
        if (!$postcode) {
            return false;
        }

        // Check if postcode matches any in our list
        $areaPcodes = array_map('trim', explode(',', $this->postcodes));
        
        foreach ($areaPcodes as $areaPostcode) {
            if (stripos($postcode, trim($areaPostcode)) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract postcode from address string
     */
    private function extractPostcodeFromAddress(string $address): ?string
    {
        // UK postcode pattern
        if (preg_match('/([A-Z]{1,2}[0-9R][0-9A-Z]?\s*[0-9][A-Z]{2})\s*$/i', $address, $matches)) {
            return strtoupper($matches[1]);
        }
        
        return null;
    }

    /**
     * Get default bin types
     */
    public static function getDefaultBinTypes(): array
    {
        return ['Food', 'Recycling', 'Garden'];
    }

    /**
     * Get bin types for this area, or default if none set
     */
    public function getBinTypesAttribute($value)
    {
        $types = json_decode($value, true);
        return !empty($types) ? $types : self::getDefaultBinTypes();
    }
}