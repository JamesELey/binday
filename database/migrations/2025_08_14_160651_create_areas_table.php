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
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('postcodes')->nullable(); // Comma-separated postcodes
            $table->boolean('active')->default(true);
            $table->enum('type', ['polygon', 'postcode'])->default('postcode');
            $table->json('coordinates')->nullable(); // For polygon areas
            $table->json('bin_types')->nullable(); // Array of bin types allowed
            $table->timestamps();
            
            // Indexes for performance
            $table->index('active');
            $table->index('type');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
