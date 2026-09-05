<?php

namespace App\Console\Commands;

use App\Mail\LowStockAdminMail;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class LowStockNotifyCommand extends Command
{
    protected $signature = 'shop:low-stock-notify';

    protected $description = 'Send low stock email to admin addresses';

    public function handle(): int
    {
        $threshold = (int) config('shop.low_stock_threshold', 5);
        $lines = ProductVariant::query()
            ->with('product')
            ->where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->limit(50)
            ->get()
            ->map(fn ($v) => $v->product->name.' / '.$v->size.' / '.$v->color.' — stock: '.$v->stock)
            ->all();
        if ($lines === []) {
            $this->info('No low stock items.');

            return self::SUCCESS;
        }
        $mailable = new LowStockAdminMail($lines, $threshold);
        foreach (config('shop.admin_notify_emails', []) as $email) {
            if ($email) {
                Mail::to($email)->queue($mailable);
            }
        }
        $this->info('Queued low stock notification.');

        return self::SUCCESS;
    }
}
