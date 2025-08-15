<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('notes');
            $table->unsignedBigInteger('parent_collection_id')->nullable()->after('is_recurring');
            $table->timestamp('last_generated_at')->nullable()->after('parent_collection_id');
            
            // Add foreign key constraint for parent collection
            $table->foreign('parent_collection_id')->references('id')->on('collections')->onDelete('set null');
            
            // Add index for recurring collections lookup
            $table->index(['is_recurring', 'collection_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropForeign(['parent_collection_id']);
            $table->dropIndex(['is_recurring', 'collection_date']);
            $table->dropColumn(['is_recurring', 'parent_collection_id', 'last_generated_at']);
        });
    }
};
