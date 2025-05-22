<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('cta_text');
            $table->string('image_path');
            $table->boolean('is_active')->default(false);
            $table->timestamp('valid_until')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // For products that are on promotion
        Schema::create('promotion_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('discount_price', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_products');
        Schema::dropIfExists('promotions');
    }
};