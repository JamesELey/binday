# Recurring Collections Setup Guide

## 🔄 Recurring Collections Feature

This feature allows customers to set up automatic recurring collections that are generated every 2 weeks on the same day of the week.

## 📋 How It Works

1. **Customer Sets Recurring**: When booking a collection, customers can check "Make this a recurring collection"
2. **Automatic Generation**: A cron job runs daily at midnight to check for recurring collections
3. **Future Scheduling**: New collections are created exactly 2 weeks from the original recurring collection date
4. **Parent-Child Relationship**: Generated collections maintain a link to their parent recurring collection

## 🚀 Server Setup (Production)

### 1. Add to Server Crontab
```bash
# Edit the crontab
sudo crontab -e

# Add this line to run Laravel's scheduler every minute
* * * * * cd /path/to/your/laravel/project && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Alternative: Direct Command (if you prefer)
```bash
# Run directly at midnight every day
0 0 * * * cd /path/to/your/laravel/project && php artisan collections:generate-recurring >> /var/log/recurring-collections.log 2>&1
```

## 🧪 Testing Commands

### Test the Recurring Generation
```bash
# Dry run (shows what would be generated without creating)
php artisan collections:generate-recurring --dry-run

# Actually generate recurring collections
php artisan collections:generate-recurring
```

### Manual Testing
```bash
# Create a test recurring collection from 2 weeks ago
php artisan tinker
>>> $collection = App\Collection::where('customer_email', 'customer@binday.com')->first()
>>> $collection->update(['is_recurring' => true, 'collection_date' => now()->subWeeks(2)])
>>> exit

# Then run the command to see it generate a new one
php artisan collections:generate-recurring --dry-run
```

## 📊 Database Schema

### New Fields Added to `collections` Table:
- `is_recurring` (boolean): Marks if this collection repeats every 2 weeks
- `parent_collection_id` (foreign key): Links generated collections to their original recurring collection
- `last_generated_at` (timestamp): Tracks when the last recurring collection was generated from this parent

## 🎯 Key Features

### Customer Experience:
- ✅ Simple checkbox when booking: "Make this a recurring collection (every 2 weeks)"
- ✅ Clear indication in "My Collections" with 🔄 Recurring badge
- ✅ Automatic scheduling - no manual re-booking needed

### Admin/Worker Features:
- ✅ Full visibility of recurring vs one-time collections
- ✅ Parent-child relationship tracking
- ✅ Automated generation with logging
- ✅ Prevents duplicate generation for the same date

### Technical Features:
- ✅ Robust cron scheduling with overlap prevention
- ✅ Detailed logging to `storage/logs/recurring-collections.log`
- ✅ Dry-run capability for testing
- ✅ Error handling and recovery
- ✅ Database relationships for audit trail

## 🔍 Monitoring

### Check Logs
```bash
# View recurring collection generation logs
tail -f storage/logs/recurring-collections.log

# View Laravel scheduler logs
tail -f storage/logs/laravel.log
```

### Verify Cron is Running
```bash
# Check if Laravel scheduler is working
php artisan schedule:list

# Test the schedule manually
php artisan schedule:run
```

## 💡 Usage Examples

### Customer Books Recurring Food Collection
1. Customer visits `/collections/create`
2. Fills out form and checks "🔄 Make this a recurring collection"
3. Books collection for Aug 15, 2025
4. System automatically creates new collections for:
   - Aug 29, 2025
   - Sep 12, 2025
   - Sep 26, 2025
   - And so on...

### Worker Views Collections
- Original collection shows "🔄 Recurring" badge
- Generated collections show normal status but link back to parent
- Full audit trail of when collections were auto-generated

## 🛠️ Troubleshooting

### No Collections Being Generated
1. Check cron is running: `php artisan schedule:list`
2. Verify recurring collections exist: `php artisan collections:generate-recurring --dry-run`
3. Check logs: `tail storage/logs/recurring-collections.log`

### Duplicate Collections
- The system prevents duplicates by checking existing collections for the target date
- Each recurring collection can only generate one collection per future date

### Performance
- Command is optimized to only process collections from exactly 2 weeks ago
- Uses database indexes for efficient querying
- Includes overlap prevention to avoid multiple instances running
