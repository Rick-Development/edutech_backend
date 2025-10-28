<?php

namespace Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Core\Traits\ApiResponse;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Models\Transaction;

class DepositController extends Controller
{
    use ApiResponse;

    public function requestDeposit()
    {
        $user = request()->user();
        $data = request()->validate([
            'amount' => 'required|numeric|min:100',
            'payment_method' => 'required|in:bank_transfer,ussd,card',
        ]);

        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

        // In real app: redirect to payment gateway
        // For now: simulate pending deposit
        $transaction = Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'deposit',
            'amount' => $data['amount'],
            'status' => 'pending',
            'reference' => 'DEP_' . strtoupper(uniqid()),
            'description' => 'Deposit via ' . $data['payment_method'],
            'meta' => $data,
        ]);

        return $this->success([
            'transaction_id' => $transaction->id,
            'amount' => $transaction->amount,
            'payment_method' => $data['payment_method'],
            'message' => 'Deposit request created. Complete payment to confirm.'
        ]);
    }
}