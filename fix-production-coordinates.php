<?php
/**
 * Production Database Coordinate Format Fix
 * 
 * This script fixes the coordinate format in the production database
 * by swapping [lng, lat] to [lat, lng] for all polygon areas.
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 Fixing coordinate format in production database...\n\n";

// Get all polygon areas
$areas = App\Area::where('type', 'polygon')->get();

if ($areas->isEmpty()) {
    echo "❌ No polygon areas found in database!\n";
    exit(1);
}

echo "📋 Found {$areas->count()} polygon areas:\n";
foreach ($areas as $area) {
    echo "  • {$area->name}\n";
}

echo "\n🔍 Analyzing coordinate formats...\n";

$needsFixing = [];
$alreadyCorrect = [];

foreach ($areas as $area) {
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
            $needsFixing[] = $area;
            echo "❌ {$area->name}: [lng, lat] format - needs fixing\n";
        } else if ($first > 50 && $first < 60 && $second < 0 && $second > -5) {
            $alreadyCorrect[] = $area;
            echo "✅ {$area->name}: [lat, lng] format - correct\n";
        } else {
            echo "⚠️  {$area->name}: Unknown format - " . json_encode($firstCoord) . "\n";
        }
    }
}

echo "\n📊 Analysis Results:\n";
echo "• ✅ Already correct: " . count($alreadyCorrect) . " areas\n";
echo "• ❌ Need fixing: " . count($needsFixing) . " areas\n";

if (empty($needsFixing)) {
    echo "\n🎉 All areas already have correct coordinate format!\n";
    exit(0);
}

echo "\n🔧 Fixing coordinate formats...\n";

$fixed = 0;
foreach ($needsFixing as $area) {
    echo "🔄 Fixing {$area->name}...\n";
    
    // Swap all coordinates from [lng, lat] to [lat, lng]
    $fixedCoordinates = [];
    foreach ($area->coordinates as $coord) {
        if (is_array($coord) && count($coord) == 2) {
            $fixedCoordinates[] = [$coord[1], $coord[0]]; // Swap [lng, lat] to [lat, lng]
        }
    }
    
    echo "  • Before: " . json_encode($area->coordinates[0]) . "\n";
    echo "  • After:  " . json_encode($fixedCoordinates[0]) . "\n";
    
    // Update the area
    $area->update(['coordinates' => $fixedCoordinates]);
    $fixed++;
    echo "  • ✅ Updated successfully\n\n";
}

echo "🎉 Coordinate format fix complete!\n";
echo "📊 Summary:\n";
echo "• Fixed: {$fixed} areas\n";
echo "• Total polygon areas: {$areas->count()}\n";

// Final verification
echo "\n🔍 Final verification:\n";
$verifyAreas = App\Area::where('type', 'polygon')->get();
foreach ($verifyAreas as $area) {
    if ($area->coordinates && !empty($area->coordinates)) {
        $coord = $area->coordinates[0];
        $format = ($coord[0] > 50 && $coord[0] < 60) ? '[lat, lng] ✅' : '[lng, lat] ❌';
        echo "• {$area->name}: {$format}\n";
    }
}

echo "\n✅ All polygon areas should now display correctly on the map!\n";
echo "🗺️  Visit the map page to verify the fix.\n";
