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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('phone')->nullable();
            $table->text('address');
            $table->string('bin_type'); // Food, Recycling, Garden, etc.
            $table->date('collection_date');
            $table->time('collection_time')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'collected', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 11, 7)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Links to user who created it
            $table->foreignId('area_id')->nullable()->constrained()->onDelete('set null'); // Links to area
            $table->timestamps();
            
            // Indexes for performance
            $table->index('collection_date');
            $table->index('status');
            $table->index('bin_type');
            $table->index('customer_email');
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
