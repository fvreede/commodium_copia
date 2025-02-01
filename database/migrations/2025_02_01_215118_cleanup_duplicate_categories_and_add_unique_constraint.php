<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Step 1: Keep only the earliest entry for each category name and 
        // move its subcategories to that entry
        $duplicates = DB::table('categories')
            ->select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            // Get all IDs for this category name, ordered by creation date
            $categoryIds = DB::table('categories')
                ->where('name', $duplicate->name)
                ->orderBy('created_at')
                ->pluck('id')
                ->toArray();

            // First ID is the one we'll keep
            $keepId = array_shift($categoryIds);

            // Update all subcategories to point to the category we're keeping
            DB::table('subcategories')
                ->whereIn('category_id', $categoryIds)
                ->update(['category_id' => $keepId]);

            // Delete the duplicate categories
            DB::table('categories')
                ->whereIn('id', $categoryIds)
                ->delete();
        }

        // Step 2: Add unique constraint
        Schema::table('categories', function (Blueprint $table) {
            $table->unique('name');
        });
    }

    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};