<?php

namespace Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Core\Traits\ApiResponse;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Models\Transaction;
use Modules\Wallet\Models\WithdrawalRequest;

class WalletController extends Controller
{
    use ApiResponse;

    public function getBalance()
    {
        $user = request()->user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);
        return $this->success(['balance' => $wallet->balance]);
    }

    public function getTransactions()
    {
        $user = request()->user();
        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet) {
            return $this->success([]);
        }
        $transactions = Transaction::where('wallet_id', $wallet->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return $this->success($transactions);
    }
}