<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Area;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        echo "🔧 Fixing polygon coordinate formats...\n";
        
        // Fix coordinate format for polygon areas
        $polygonAreas = Area::where('type', 'polygon')->get();
        
        if ($polygonAreas->isEmpty()) {
            echo "ℹ️  No polygon areas found to fix.\n";
            return;
        }
        
        $fixed = 0;
        $alreadyCorrect = 0;
        
        foreach ($polygonAreas as $area) {
            if (!$area->coordinates || empty($area->coordinates)) {
                echo "⏩ Skipping {$area->name} (no coordinates)\n";
                continue;
            }
            
            $firstCoord = $area->coordinates[0];
            
            if (is_array($firstCoord) && count($firstCoord) == 2) {
                $first = $firstCoord[0];
                $second = $firstCoord[1];
                
                // UK coordinates: Latitude ~52-55, Longitude ~-5 to 2
                // If first value is negative and around -2 to -3, it's likely longitude (needs swapping)
                if ($first < 0 && $first > -5 && $second > 50 && $second < 60) {
                    echo "🔄 Fixing {$area->name} coordinates (swapping lat/lng)...\n";
                    
                    // Swap all coordinates from [lng, lat] to [lat, lng]
                    $fixedCoordinates = [];
                    foreach ($area->coordinates as $coord) {
                        if (is_array($coord) && count($coord) == 2) {
                            $fixedCoordinates[] = [$coord[1], $coord[0]]; // Swap [lng, lat] to [lat, lng]
                        }
                    }
                    
                    $area->update(['coordinates' => $fixedCoordinates]);
                    $fixed++;
                } else if ($first > 50 && $first < 60 && $second < 0 && $second > -5) {
                    echo "✅ {$area->name} already has correct [lat, lng] format\n";
                    $alreadyCorrect++;
                } else {
                    echo "⚠️  {$area->name} has unknown coordinate format: " . json_encode($firstCoord) . "\n";
                }
            }
        }
        
        echo "\n📊 Coordinate fix summary:\n";
        echo "• Fixed: {$fixed} areas\n";
        echo "• Already correct: {$alreadyCorrect} areas\n";
        echo "• Total polygon areas: {$polygonAreas->count()}\n";
        
        if ($fixed > 0) {
            echo "✅ Polygon areas should now display correctly on the map!\n";
        }
        
        // Additional database integrity checks
        echo "\n🔍 Performing database integrity checks...\n";
        
        // Check for orphaned collections (collections without valid areas)
        $collectionsWithoutAreas = \App\Collection::whereNotNull('area_id')
            ->whereNotExists(function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('areas')
                    ->whereColumn('areas.id', 'collections.area_id');
            })->count();
            
        if ($collectionsWithoutAreas > 0) {
            echo "⚠️  Found {$collectionsWithoutAreas} collections with invalid area references\n";
            echo "  These will be set to NULL area_id\n";
            
            \App\Collection::whereNotNull('area_id')
                ->whereNotExists(function ($query) {
                    $query->select(\Illuminate\Support\Facades\DB::raw(1))
                        ->from('areas')
                        ->whereColumn('areas.id', 'collections.area_id');
                })->update(['area_id' => null]);
        }
        
        // Check for orphaned collections (collections without valid users)
        $collectionsWithoutUsers = \App\Collection::whereNotNull('user_id')
            ->whereNotExists(function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'collections.user_id');
            })->count();
            
        if ($collectionsWithoutUsers > 0) {
            echo "⚠️  Found {$collectionsWithoutUsers} collections with invalid user references\n";
            echo "  These will be set to NULL user_id\n";
            
            \App\Collection::whereNotNull('user_id')
                ->whereNotExists(function ($query) {
                    $query->select(\Illuminate\Support\Facades\DB::raw(1))
                        ->from('users')
                        ->whereColumn('users.id', 'collections.user_id');
                })->update(['user_id' => null]);
        }
        
        echo "✅ Database integrity checks completed!\n";
        echo "\n🎉 Migration completed! All systems should now use database storage exclusively.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "⚠️  This migration cannot be automatically reversed.\n";
        echo "   Coordinate format changes are one-way transformations.\n";
        echo "   If needed, restore from database backup.\n";
    }
};
