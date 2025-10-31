<?php

namespace Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Core\Traits\ApiResponse;
use Modules\Wallet\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Modules\Wallet\Services\FlutterwaveService;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Models\Transaction;

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

public function initiateTransfer(Request $request)
{
        $user = request()->user();
        $data = request()->validate([
            'amount' => 'required|numeric|min:100,max:100000000',
            'account_number' => 'required|digits:10',
            'bank_code' => 'required|string',
            'account_name' => 'required|string',
        ]);
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

        if ($wallet->balance < $request->amount) {
            return $this->error('Insufficient balance', 400);
        }

        try{

            $reference = 'WDR' . strtoupper(uniqid());
    $data = [
        "action" => "instant",
        "payment_instruction" => [
            "source_currency" => "NGN",
            "amount" => [
                "applies_to" => "source_currency",
                "value" => $request->amount
            ],
            "recipient" => [
                "bank" => [
                    "account_number" => $request->account_number,
                    "code" => $request->bank_code
                ]
            ],
            "sender" => [
                "name" => [
                    "first" => $user->firstname,
                    "last" => $user->lastname
                ],
                "email" => $user->email,
            ],
            "destination_currency" => "NGN"
        ],
        "type" => "bank",
        "reference" => $reference
    ];

    $flutterwaveService = new FlutterwaveService();
    $response = $flutterwaveService->initiateTransfer($data);


        if (isset($response['status']) && $response['status'] === 'success') {


        $wallet->decrement('balance', $request->amount);
        $wallet->increment('pending_balance', $request->amount);
        $wallet->save();
        Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'withdrawal',
            'amount' => $request->amount,
            'status' => 'pending',
            'reference' => $reference,
            'description' => 'Withdrawal Through Bank Transfer',
            'meta' => $data,
        ]);

        // id	user_id	amount	account_number	account_name	bank_name	status	created_at	updated_at	deleted_at
        WithdrawalRequest::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'bank_name' => $request->bank_code,
            'status' => 'pending',
            'reference' => $reference,
        ]);
            return $this->success($response, $response['message']);
        }

    return $this->error('Withdrawal initiation failed', 500);
}catch (\Exception $e) {
    return $this->error('Withdrawal error: ' . $e->getMessage(), 500);
}

}


// getBanks
public function getBanks()
{
    try {
        $flutterwave = new FlutterwaveService();
        $response = $flutterwave->getBanks(['country' => 'NG']);

        if (isset($response['status']) && $response['status'] === 'success') {
            return $this->success($response['data'], $response['message']);
        }

        return $this->error('Failed to fetch banks', 500);

    } catch (\Exception $e) {
        return $this->error('Error: ' . $e->getMessage(), 500);
    }
}



public function resolveAccount(Request $request)
{
    $data = $request->validate([
        'account_number' => 'required|digits:10',
        'bank_code' => 'required|string',
    ]);

    try {
        $flutterwave = new FlutterwaveService();
        $response = $flutterwave->resolveAccount([
            'account' => [
                'code' => $data['bank_code'],
                'number' => $data['account_number'],
            ],
            'currency' => 'NGN'
        ]);


        if (isset($response['status']) && $response['status'] === 'success') {
            return $this->success($response['data'], $response['message']);
        }

        return $this->error('The provided account number is invalid.', 500);

    } catch (\Exception $e) {
        return $this->error('The provided account number is invalid.', 500);
    }

}
}