<?php

namespace App\Services;

use App\Models\Order;

class BankTransferPaymentService
{
    public function isConfigured(): bool
    {
        return filled(config('shop.bank_transfer.bank_code'))
            && filled(config('shop.bank_transfer.account_number'))
            && filled(config('shop.bank_transfer.account_name'));
    }

    public function detailsFor(Order $order): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $bankCode = (string) config('shop.bank_transfer.bank_code');
        $accountNumber = (string) config('shop.bank_transfer.account_number');
        $accountName = (string) config('shop.bank_transfer.account_name');
        $transferContent = substr('QUDENA '.$order->order_number, 0, 25);
        $query = http_build_query([
            'amount' => (int) round((float) $order->total),
            'addInfo' => $transferContent,
            'accountName' => $accountName,
        ]);

        return [
            'bank_name' => (string) config('shop.bank_transfer.bank_name', $bankCode),
            'account_number' => $accountNumber,
            'account_name' => $accountName,
            'amount' => (float) $order->total,
            'transfer_content' => $transferContent,
            'qr_url' => 'https://img.vietqr.io/image/'.rawurlencode($bankCode).'-'.rawurlencode($accountNumber).'-compact2.png?'.$query,
        ];
    }
}
