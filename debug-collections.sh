#!/bin/bash

echo "🔍 Collections Page Debug Script"
echo "==============================="

cd /var/www/binday

echo "1. 📋 Checking if collections table exists..."
php artisan tinker --execute="
try {
    \$count = DB::table('collections')->count();
    echo 'Collections table exists with ' . \$count . ' records\n';
} catch (Exception \$e) {
    echo 'Collections table error: ' . \$e->getMessage() . '\n';
}
"

echo ""
echo "2. 🗄️ Checking all database tables..."
php artisan tinker --execute="
try {
    \$tables = DB::select('SHOW TABLES');
    echo 'Database tables:\n';
    foreach(\$tables as \$table) {
        \$tableName = array_values((array)\$table)[0];
        echo '  - ' . \$tableName . '\n';
    }
} catch (Exception \$e) {
    echo 'Database error: ' . \$e->getMessage() . '\n';
}
"

echo ""
echo "3. 🏃‍♂️ Running migrations..."
php artisan migrate --force

echo ""
echo "4. 🌱 Seeding demo collections if needed..."
php artisan tinker --execute="
try {
    \$count = App\Collection::count();
    if (\$count == 0) {
        // Create a demo collection
        App\Collection::create([
            'customer_name' => 'Demo Customer',
            'customer_email' => 'demo@example.com',
            'address' => 'Demo Address, Eccleshall',
            'bin_type' => 'General',
            'collection_date' => date('Y-m-d', strtotime('+7 days')),
            'status' => 'pending',
            'user_id' => 1,
            'area_id' => 1
        ]);
        echo 'Demo collection created\n';
    } else {
        echo 'Collections already exist (' . \$count . ' collections)\n';
    }
} catch (Exception \$e) {
    echo 'Error creating demo collection: ' . \$e->getMessage() . '\n';
}
"

echo ""
echo "5. 🔍 Testing collections query..."
php artisan tinker --execute="
try {
    \$collections = App\Collection::with(['area', 'user'])->get();
    echo 'Collections query successful, found ' . \$collections->count() . ' collections\n';
    if (\$collections->count() > 0) {
        \$first = \$collections->first();
        echo 'First collection: ID=' . \$first->id . ', Customer=' . \$first->customer_name . '\n';
    }
} catch (Exception \$e) {
    echo 'Collections query error: ' . \$e->getMessage() . '\n';
}
"

echo ""
echo "6. 📋 Recent Laravel errors..."
if [ -f "storage/logs/laravel.log" ]; then
    echo "Last 10 error lines:"
    tail -10 storage/logs/laravel.log
else
    echo "No Laravel log file found"
fi

echo ""
echo "7. ⚡ Clear caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo ""
echo "✅ Debug complete! Try visiting /collections again"
