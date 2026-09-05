<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('avg_cost', 12, 2)->default(0)->after('price');
            $table->decimal('last_cost', 12, 2)->default(0)->after('avg_cost');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['avg_cost', 'last_cost']);
        });
    }
};
