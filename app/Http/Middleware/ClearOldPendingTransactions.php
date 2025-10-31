<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Modules\Wallet\Models\Transaction; 
use Modules\Wallet\Models\Wallet; 
use Illuminate\Support\Facades\Log; // Added for explicit logging

class ClearOldPendingTransactions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ensure the user is authenticated before proceeding
        if (!$request->user()) {
            return $next($request);
        }
        
        $user = $request->user();
        $minutes = 10;
        $failedCount = 0;
        
        // --- 1. Find the current user's wallet ---
        $wallet = Wallet::where('user_id', $user->id)->first();

        // If the user doesn't even have a wallet, there's nothing to clean up.
        if (!$wallet) {
            return $next($request);
        }

        // --- 2. Get the current user's eligible pending transactions ---
        $pendingTransactions = Transaction::where('wallet_id', $wallet->id) // FILTER BY WALLET ID
            ->where('status', 'pending')
            ->where('type', 'deposit')
            ->where('created_at', '<=', now()->subMinutes($minutes))
            ->get(); // Using get() to iterate

        // If the user has transactions, but their pending_balance is already zero, skip the loop for efficiency
        if ($wallet->pending_balance > 0 && $pendingTransactions->isNotEmpty()) {

            foreach ($pendingTransactions as $transaction) {
                
                // Determine the amount to clear from pending_balance
                // It should not exceed the current pending_balance or the transaction amount
                $amountToDeduct = min($transaction->amount, $wallet->pending_balance);

                // 3. Deduct the amount from the pending_balance (only if > 0)
                if ($amountToDeduct > 0) {
                    $wallet->decrement('pending_balance', $amountToDeduct);
                }

                // 4. Mark the transaction as failed
                $transaction->update([
                    'status' => 'failed',
                    // 'description' => "Failed automatically by middleware for user {$user->id}: Payment not completed within {$minutes} minute window. Pending balance cleared."
                ]);
                
                $failedCount++;
            }

            if ($failedCount > 0) {
                Log::info("Cleanup Middleware: User {$user->id} had {$failedCount} old pending transactions processed.");
            }
        }

        return $next($request);
    }
}