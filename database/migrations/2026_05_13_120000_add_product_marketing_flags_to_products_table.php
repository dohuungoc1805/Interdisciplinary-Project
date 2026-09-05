<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_hot')->default(false)->after('is_featured');
            $table->boolean('is_on_sale')->default(false)->after('is_hot');
            $table->boolean('is_new')->default(false)->after('is_on_sale');
        });

        // Backfill from existing data
        if (Schema::hasTable('products')) {
            DB::table('products')->where('is_featured', true)->update(['is_hot' => true]);
            DB::table('products')
                ->whereNotNull('compare_price')
                ->whereColumn('compare_price', '>', 'price')
                ->update(['is_on_sale' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_hot', 'is_on_sale', 'is_new']);
        });
    }
};
