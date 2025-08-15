<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Collection;
use Carbon\Carbon;

class GenerateRecurringCollections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collections:generate-recurring {--dry-run : Show what would be generated without creating records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new collections for recurring collections that are due';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Checking for recurring collections to generate...');
        
        $today = Carbon::today();
        $twoWeeksFromToday = $today->copy()->addWeeks(2);
        
        // Find all recurring collections from exactly 2 weeks ago
        $recurringDate = $today->copy()->subWeeks(2);
        
        $this->info("📅 Looking for recurring collections from {$recurringDate->format('Y-m-d')} to generate for {$twoWeeksFromToday->format('Y-m-d')}");
        
        // Get recurring collections from 2 weeks ago that haven't been generated yet for the target date
        $recurringCollections = Collection::where('is_recurring', true)
            ->where('collection_date', $recurringDate->format('Y-m-d'))
            ->whereDoesntHave('children', function ($query) use ($twoWeeksFromToday) {
                $query->where('collection_date', $twoWeeksFromToday->format('Y-m-d'));
            })
            ->get();
        
        if ($recurringCollections->isEmpty()) {
            $this->info('✅ No recurring collections found that need generation.');
            return;
        }
        
        $this->info("🎯 Found {$recurringCollections->count()} recurring collections to process:");
        
        $generatedCount = 0;
        
        foreach ($recurringCollections as $collection) {
            $this->line("   📋 {$collection->customer_name} - {$collection->bin_type} at {$collection->address}");
            
            if ($this->option('dry-run')) {
                $this->line("      [DRY RUN] Would create collection for {$twoWeeksFromToday->format('Y-m-d')}");
                $generatedCount++;
                continue;
            }
            
            try {
                // Create the new recurring collection
                $newCollection = $collection->replicate();
                $newCollection->collection_date = $twoWeeksFromToday;
                $newCollection->parent_collection_id = $collection->id;
                $newCollection->status = 'pending'; // New collections start as pending
                $newCollection->created_at = now();
                $newCollection->updated_at = now();
                $newCollection->save();
                
                // Update the parent collection's last_generated_at timestamp
                $collection->update(['last_generated_at' => now()]);
                
                $this->line("      ✅ Created new collection #{$newCollection->id}");
                $generatedCount++;
                
            } catch (\Exception $e) {
                $this->error("      ❌ Failed to create collection: {$e->getMessage()}");
            }
        }
        
        if ($this->option('dry-run')) {
            $this->info("🔍 DRY RUN: Would have generated {$generatedCount} collections");
        } else {
            $this->info("✅ Successfully generated {$generatedCount} recurring collections");
            
            if ($generatedCount > 0) {
                $this->info("📧 Consider sending notification emails to customers about their new bookings");
            }
        }
        
        return 0;
    }
}
