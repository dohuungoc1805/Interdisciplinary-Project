<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('kind', 16)->default('product')->after('code');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->string('applied_shipping_coupon_code', 32)->nullable()->after('applied_coupon_code');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('shipping_discount_total', 12, 2)->default(0)->after('discount_total');
            $table->foreignId('shipping_coupon_id')->nullable()->after('coupon_code')->constrained('coupons')->nullOnDelete();
            $table->string('shipping_coupon_code', 32)->nullable()->after('shipping_coupon_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shipping_coupon_id']);
            $table->dropColumn(['shipping_discount_total', 'shipping_coupon_id', 'shipping_coupon_code']);
        });
        Schema::table('carts', fn (Blueprint $table) => $table->dropColumn('applied_shipping_coupon_code'));
        Schema::table('coupons', fn (Blueprint $table) => $table->dropColumn('kind'));
    }
};
