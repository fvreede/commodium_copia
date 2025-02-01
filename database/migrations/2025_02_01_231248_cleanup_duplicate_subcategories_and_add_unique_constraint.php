<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Step 1: Find and clean up any duplicate subcategory names within the same category
        $duplicates = DB::table('subcategories')
            ->select('name', 'category_id')
            ->groupBy('name', 'category_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            // Get all IDs for this subcategory name in this category, ordered by creation date
            $subcategoryIds = DB::table('subcategories')
                ->where('name', $duplicate->name)
                ->where('category_id', $duplicate->category_id)
                ->orderBy('created_at')
                ->pluck('id')
                ->toArray();

            // First ID is the one we'll keep
            $keepId = array_shift($subcategoryIds);

            // Update all products to point to the subcategory we're keeping
            DB::table('products')
                ->whereIn('subcategory_id', $subcategoryIds)
                ->update(['subcategory_id' => $keepId]);

            // Delete the duplicate subcategories
            DB::table('subcategories')
                ->whereIn('id', $subcategoryIds)
                ->delete();
        }

        // Step 2: Add unique constraint for subcategory name within each category
        Schema::table('subcategories', function (Blueprint $table) {
            $table->unique(['name', 'category_id']);
        });
    }

    public function down()
    {
        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropUnique(['name', 'category_id']);
        });
    }
};