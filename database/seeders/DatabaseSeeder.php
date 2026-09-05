<?php

namespace Database\Seeders;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Quản trị viên',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'is_blocked' => false,
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Khách hàng mẫu',
                'password' => Hash::make('password'),
                'phone' => '0901234567',
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );

        $this->call(CatalogSeeder::class);

        Coupon::query()->updateOrCreate(
            ['code' => 'WELCOME10'],
            [
                'type' => CouponType::Percent->value,
                'value' => 10,
                'min_order_amount' => 200000,
                'used_count' => 0,
                'is_active' => true,
            ]
        );

        Setting::setValue('support_email', 'support@'.parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'example.com');

        $this->call(DemoDataSeeder::class);
    }
}
