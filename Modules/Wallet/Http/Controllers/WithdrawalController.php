<?php

namespace Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Core\Traits\ApiResponse;
use Modules\Wallet\Models\WithdrawalRequest;

class WithdrawalController extends Controller
{
    use ApiResponse;

    public function requestWithdrawal()
    {
        $user = request()->user();
        $data = request()->validate([
            'amount' => 'required|numeric|min:1000',
            'account_number' => 'required|digits:10',
            'account_name' => 'required|string',
            'bank_name' => 'required|string',
        ]);

        // Check wallet balance (simplified)
        // In real app: check actual balance
        if ($data['amount'] > 100000) {
            return $this->error('Insufficient balance', 400);
        }

        $withdrawal = WithdrawalRequest::create([
            'user_id' => $user->id,
            'amount' => $data['amount'],
            'account_number' => $data['account_number'],
            'account_name' => $data['account_name'],
            'bank_name' => $data['bank_name'],
        ]);

        return $this->success($withdrawal, 'Withdrawal request submitted');
    }

    public function getWithdrawalRequests()
    {
        $user = request()->user();
        $requests = WithdrawalRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return $this->success($requests);
    }
}