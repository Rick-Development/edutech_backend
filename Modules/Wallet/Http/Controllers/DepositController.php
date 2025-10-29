<?php

namespace Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Core\Traits\ApiResponse;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Models\Transaction;
use Illuminate\Http\Request;
use Modules\Wallet\Services\FlutterwaveService;

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
        $wallet->balance += $data['amount'];
        $wallet->save();

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


public function initializeDeposit(Request $request)
{
    $user = $request->user();
    $data = $request->validate([
        'amount' => 'required|numeric|min:100',
        'currency' => 'required|in:NGN',
    ]);

    $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);
    $transaction = Transaction::create([
        'wallet_id' => $wallet->id,
        'type' => 'deposit',
        'amount' => $data['amount'],
        'status' => 'pending',
        'reference' => 'DEP_' . strtoupper(uniqid()),
    ]);

    try {
        $flutterwave = new FlutterwaveService();
        $paymentData = [
            'tx_ref' => $transaction->reference,
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'redirect_url' => route('wallet.deposit.callback'),
            'customer' => [
                'email' => $user->email,
                'name' => $user->name,
            ],
            'customizations' => [
                'title' => 'Wavecrest Institute Deposit',
                'description' => 'Fund your learning wallet',
            ],
        ];

        $response = $flutterwave->initializePayment($paymentData);

        if (isset($response['status']) && $response['status'] === 'success') {
            return $this->success([
                'authorization_url' => $response['data']['link'],
                'transaction_id' => $transaction->id,
            ]);
        }

        $transaction->update(['status' => 'failed']);
        return $this->error('Payment init failed', 500);

    } catch (\Exception $e) {
        $transaction->update(['status' => 'failed']);
        return $this->error('Payment error: ' . $e->getMessage(), 500);
    }
}


// getPaymentMethods

public function getPaymentMethods()
{
    try {
        $flutterwave = new FlutterwaveService();
        $response = $flutterwave->getPaymentMethods(['page' => 1, 'size' => 10]);

        if (isset($response['status']) && $response['status'] === 'success') {
            return $this->success($response['data'], $response['message']);
        }

        return $this->error('Failed to fetch payment methods', 500);

    } catch (\Exception $e) {
        return $this->error('Error: ' . $e->getMessage(), 500);
    }

}

//createPaymentMethod
public function deposit(Request $request)
{

    $user = $request->user();
    $requestData = $request->validate([
        'amount' => 'required|numeric|min:100',
    ]);
    
    try {
         $data = [
            "currency" => "NGN",
            "account_type" => "dynamic",
            "reference" => 'DEP' . strtoupper(uniqid()), // unique reference for the virtual account max
            "customer_id" => $user->customer_id,
            "amount" => $requestData['amount'],
            "expiry" => 600
        ];
        $flutterwave = new FlutterwaveService();
        $response = $flutterwave->createVirtualAccount($data);

        if (isset($response['status']) && $response['status'] === 'success') {
            return $this->success($response, $response['message']);
        }

        return $this->error('Failed to create payment method', 500);

    } catch (\Exception $e) {
        return $this->error('Error: ' . $e->getMessage(), 500);
    }

}

}