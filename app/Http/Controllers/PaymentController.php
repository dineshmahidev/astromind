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
        ]);

        // In a real app, you would verify balance and deduct here
        // For now, we just return success to unblock the frontend

        return response()->json([
            'success' => true,
            'message' => 'Payment successful (Dummy Mode)',
            'transaction_id' => 'TXN_' . uniqid(),
            'amount' => $request->amount,
        ]);
    }
}
