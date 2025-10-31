<?php

namespace Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Core\Traits\ApiResponse;
use Modules\Wallet\Models\Wallet;
use Illuminate\Http\Request;
use Modules\Wallet\Models\Transaction;
use Modules\Wallet\Models\WithdrawalRequest;
use Illuminate\Support\Facades\Log;


class WalletController extends Controller
{
    use ApiResponse;


    /**
     * Get the user's formatted wallet balances.
     * * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBalance(Request $request)
    {
        $user = $request->user();
        
        // Find or create the wallet
        // Assuming your Wallet model has 'balance' (available) and 'pending_balance' fields
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

        // --- 1. Calculate and Prepare Raw Balances ---
        $availableBalance = $wallet->balance ?? 0.00;
        $pendingBalance = $wallet->pending_balance ?? 0.00;
        $totalBalance = $availableBalance + $pendingBalance;
        
        // --- 2. Format Balances ---
        // Format: 2 decimal places, period for decimal, comma for thousands
        $formatNumber = function ($number) {
            return number_format($number, 2, '.', ',');
        };

        $data = [
            'available_balance' => $formatNumber($availableBalance),
            'pending_balance' => $formatNumber($pendingBalance),
            'total_balance' => $formatNumber($totalBalance),
        ];
        
        // Return a successful JSON response
        return $this->success($data);
    }



    // Placeholder for your success response method
    protected function success(array $data)
    {
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
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
        return $this->success($transactions->toArray());
    }


    /**
     * Handle incoming Flutterwave webhooks.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function webhook(Request $request)
    {
        // 1. Log the full request payload for debugging
        Log::info('Flutterwave Webhook Received:', $request->all());

        // Get the payload
        $payload = $request->all();
        // Log::info('Flutterwave Webhook headers:', $request->headers->all());

        // **IMPORTANT SECURITY STEP: VERIFY THE WEBHOOK SIGNATURE**
        // $secretHash = env('FLW_SECRET_HASH_V4'); // Ensure this is set in your .env
        // $signature = $request->header('secret-hash') ?? $request->header('flutterwave-signature');
        
        // if (empty($signature) || ($signature !== $secretHash)) {
        //     // Discard request that is not from Flutterwave
        //     Log::warning('Flutterwave Webhook: Invalid Signature', ['signature' => $signature]);
        //     return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
        // }

        // 2. Determine the type of event
        $eventType = $payload['type'] ?? $payload['event'];
        $eventData = $payload['data'];

        try {
            switch ($eventType) {
                case 'charge.completed':
                    $this->handleDeposit($eventData);
                    break;

                case 'transfer.disburse':
                    $this->handleTransferDisburse($payload);
                    break;
                
                // Add more cases for other events (e.g., failed transfers, etc.) if needed
                default:
                    Log::info("Flutterwave Webhook: Unhandled Event Type: {$eventType}");
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Flutterwave Webhook Processing Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'payload' => $payload]);
            // Still return 200 to prevent Flutterwave from retrying indefinitely
        }

        // 3. Acknowledge the webhook immediately (HTTP 200 OK)
        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Handle a successful deposit (charge.completed event).
     * @param array $data
     */
    protected function handleDeposit(array $data)
    {
        $tx_ref = $data['tx_ref'] ?? $data['reference'];
        $status = $data['status'] ?? $data['data']['status'] ?? null;
        $amount = $data['charged_amount'] ?? $data['amount'];
        $email = $data['customer']['email'] ?? $data['customer']['data']['email'] ?? null;

        // **A. Find the corresponding pending transaction (or user wallet)**
        // Assuming you created a PENDING transaction in your system when displaying the payment link/details.
        // If not, you'll need to look up the user/wallet via the 'email' or a custom 'meta' field in tx_ref.
        $transaction = Transaction::where('reference', $tx_ref)
                                  ->where('status', 'pending')
                                  ->first();

        if (!$transaction) {
            Log::warning('Deposit Webhook: No pending transaction found for reference.', ['tx_ref' => $tx_ref]);
            return;
        }

        // **B. Verify the amount (Crucial Security Check)**
        // This is where you compare the webhook amount to the amount you expected.
        // Assuming your Wallet Transaction amount is in NGN.
        if ($transaction->amount != $amount || $data['currency'] !== 'NGN') {
            Log::error('Deposit Webhook: Amount Mismatch/Currency Error.', [
                'expected' => $transaction->amount, 
                'received' => $amount, 
                'tx_ref' => $tx_ref
            ]);
            // You might want to flag this for manual review instead of failing silently
            $transaction->update(['status' => 'failed', 'description' => 'Amount Mismatch from Flutterwave Webhook']);
            return;
        }
        
        // **C. Process if successful**
        if ($status === 'successful' || $status === 'succeeded') {
            // 1. Update the transaction status
            $transaction->update([
                'status' => 'completed',
                'description' => 'Deposit successful via Flutterwave Bank Transfer.',
                'meta' => array_merge($transaction->meta ?? [], $data)
            ]);

            // 2. Credit the user's wallet (Assuming a Wallet model exists)
            $wallet = $transaction->wallet;
            if ($wallet) {
                $wallet->increment('balance', $amount);
                $wallet->decrement('pending_balance', $amount);
            }
            
            Log::info("Deposit Webhook: Successfully processed deposit for {$tx_ref}. Amount: {$amount}");

        } else {
            // Transaction failed or was not completed
            $transaction->update([
                'status' => 'failed',
                'description' => 'Deposit failed: ' . $data['processor_response'],
                'meta' => array_merge($transaction->meta ?? [], $data)
            ]);
            Log::warning("Deposit Webhook: Transaction failed for {$tx_ref}. Status: {$status}");
        }
    }

    /**
     * Handle a transfer status update (transfer.disburse event).
     * @param array $payload
     */
    protected function handleTransferDisburse(array $payload)
    {
        $data = $payload['data'];
        $reference = $data['reference']; // The flw_... reference from your log
        $status = $data['status']; // SUCCESSFUL or FAILED
        $amount = $data['amount']; // The amount disbursed (excluding fee)

        // **A. Find the corresponding Withdrawal Request**
        // Assuming the 'reference' in your log (e.g., 'flw_5uLctGhyPu') is stored in the 
        // WithdrawalRequest model's 'meta' field or another reference column.
        $withdrawalRequest = WithdrawalRequest::where('status', 'pending') // or 'pending' if you update upon API call
                                            ->where('reference', $reference) // Assuming 'reference' is used for the FLW reference
                                            ->first();
        
        if (!$withdrawalRequest) {
            // If the FLW reference wasn't stored in the 'reference' column, you might search 
            // the Transaction table for the original debit transaction instead.
            Log::warning('Withdrawal Webhook: No approved withdrawal request found for reference.', ['reference' => $reference]);
            return;
        }
        // return;

        // Transaction::create([
        //     'wallet_id' => $wallet->id,
        //     'type' => 'deposit',
        //     'amount' => $requestData['amount'],
        //     'status' => 'pending',
        //     'reference' => $reference,
        //     'description' => 'Deposit Through Dynamic Virtual Account',
        //     'meta' => $data,
        // ]);
        // **B. Update the Withdrawal Request status**
        if ($status === 'SUCCESSFUL') {
            $withdrawalRequest->update(['status' => 'approved']);
            Log::info("Withdrawal Webhook: Successfully completed withdrawal request #{$withdrawalRequest->id}. FLW Ref: {$reference}");

            // Create a corresponding completed transaction record if you haven't already
            // This is often done when the request is created, but ensuring it's marked 'completed' now.
            $transaction = Transaction::where('reference', $withdrawalRequest->reference)->first();
            if ($transaction) {
                 $transaction->update(['status' => 'completed']);
            }

            $wallet = $transaction->wallet;
            if ($wallet) {
                $wallet->decrement('pending_balance', $withdrawalRequest->amount);
            }

        } elseif ($status === 'FAILED') {
            // If transfer fails, the funds usually need to be reversed to the user's wallet
            $withdrawalRequest->update(['status' => 'rejected']);

            $providerResponse = $data['provider_response']['message'] ?? 'Transfer failed with no specific message.';

            // **C. Revert the funds back to the user's wallet**
            $wallet = $withdrawalRequest->user->wallet; 
            if ($wallet) {
                // Funds to revert is the withdrawal amount.
                $wallet->increment('balance', $withdrawalRequest->amount);
                
                // Record the reversal transaction
                Transaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'reversal',
                    'amount' => $withdrawalRequest->amount,
                    'status' => 'completed',
                    'reference' => 'REVERSAL_' . $reference,
                    'description' => 'Failed withdrawal fund reversal. Reason: ' . $providerResponse,
                    'meta' => $payload
                ]);
            }
            
            Log::error("Withdrawal Webhook: Transfer failed for request #{$withdrawalRequest->id}. Funds reversed. Reason: {$providerResponse}");
        }
    }
}