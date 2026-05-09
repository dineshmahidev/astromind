<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Dummy payment endpoint for testing.
     */
    public function dummy(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'amount' => 'required|numeric',
            'type' => 'nullable|string|in:credit,debit'
        ]);

        $user = \App\Models\User::find($request->user_id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $type = $request->type ?? 'debit';

        if ($type === 'credit') {
            // Top up
            $user->increment('wallet_balance', $request->amount);
            $message = 'Top-up successful (Mock)';
        } else {
            // Consultation Payment
            if ($user->wallet_balance < $request->amount) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Insufficient balance. Your balance is ₹' . number_format($user->wallet_balance, 2) . '. Please top up your wallet.'
                ], 400);
            }
            $user->decrement('wallet_balance', $request->amount);
            $message = 'Payment successful (Mock)';
        }

        // Save Transaction Record
        $transaction = \App\Models\Transaction::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'type' => $type,
            'status' => 'completed',
            'payment_id' => 'TXN_' . strtoupper(uniqid()),
            'currency' => 'INR'
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'transaction_id' => $transaction->payment_id,
            'amount' => $request->amount,
            'new_balance' => $user->wallet_balance
        ]);
    }
}
