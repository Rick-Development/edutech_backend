<?php

use Illuminate\Support\Facades\Route;
use Modules\Wallet\Http\Controllers\WalletController;
use Modules\Wallet\Http\Controllers\DepositController;
use Modules\Wallet\Http\Controllers\WithdrawalController;

Route::prefix('wallet')->middleware('auth:sanctum')->group(function () {
    // Balance & Transactions
    Route::get('/balance', [WalletController::class, 'getBalance']);
    Route::get('/transactions', [WalletController::class, 'getTransactions']);

    // Deposits
    Route::post('/deposit', [DepositController::class, 'requestDeposit']);

    // Withdrawals
    Route::post('/withdraw', [WithdrawalController::class, 'requestWithdrawal']);
    Route::get('/withdrawals', [WithdrawalController::class, 'getWithdrawalRequests']);
});