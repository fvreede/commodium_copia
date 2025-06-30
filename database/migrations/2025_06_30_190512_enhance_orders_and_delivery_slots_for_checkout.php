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
        Schema::table('orders', function (Blueprint $table) {
            // Add payment method if it doesn't exist
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('total');
            }
            
            // Add payment status if it doesn't exist
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])
                      ->default('pending')
                      ->after('payment_method');
            }
            
            // Rename notes to order_notes if notes exists
            if (Schema::hasColumn('orders', 'notes') && !Schema::hasColumn('orders', 'order_notes')) {
                $table->renameColumn('notes', 'order_notes');
            } elseif (!Schema::hasColumn('orders', 'order_notes')) {
                // Add order_notes if it doesn't exist
                $table->text('order_notes')->nullable()->after('delivery_address');
            }
            
            // Add order_date if it doesn't exist
            if (!Schema::hasColumn('orders', 'order_date')) {
                $table->timestamp('order_date')->nullable()->after('order_notes');
            }
            
            // Ensure order_number exists and is unique
            if (!Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number')->unique()->nullable()->after('id');
            }
        });
        
        // Update delivery_slots table to track current availability
        Schema::table('delivery_slots', function (Blueprint $table) {
            // Add current_available column if it doesn't exist
            if (!Schema::hasColumn('delivery_slots', 'current_available')) {
                $table->integer('current_available')->default(0)->after('available_slots');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = ['payment_method', 'payment_status', 'order_date'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
            
            // Note: We don't reverse the rename of notes to order_notes
            // as it might cause data loss
        });
        
        Schema::table('delivery_slots', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_slots', 'current_available')) {
                $table->dropColumn('current_available');
            }
        });
    }
};