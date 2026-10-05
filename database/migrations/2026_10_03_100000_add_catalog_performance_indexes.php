<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'category_id'], 'products_active_category_index');
            $table->index(['is_active', 'is_on_sale'], 'products_active_sale_index');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->index(['product_id', 'stock'], 'variants_product_stock_index');
            $table->index(['size', 'color', 'stock'], 'variants_size_color_stock_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_category_index');
            $table->dropIndex('products_active_sale_index');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex('variants_product_stock_index');
            $table->dropIndex('variants_size_color_stock_index');
        });
    }
};
