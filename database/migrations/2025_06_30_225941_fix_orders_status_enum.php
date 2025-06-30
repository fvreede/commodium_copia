<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, safely migrate any existing data
        DB::table('orders')->where('status', 'completed')->update(['status' => 'delivered']);
        
        // Update the ENUM to include all status values your code uses
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending',          // Order placed, awaiting confirmation
                'confirmed',        // Order confirmed, ready for processing  
                'processing',       // Being picked/packed
                'out_for_delivery', // On delivery truck
                'delivered',        // Successfully delivered
                'cancelled'         // Cancelled at any stage
            ])->default('pending')->change();
            
            // Add index for better performance
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            // Revert to original enum
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->change();
        });
    }
};