<?php

namespace App\Http\Controllers;

use Chapa\Chapa\Facades\Chapa as Chapa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class ChapaController extends Controller
{
    protected $reference;

    public function __construct()
    {
        $this->reference = Chapa::generateReference();
    }

    /**
     * Initialize Chapa payment
     */
    public function initialize(Request $request)
    {
        // Validate email
        $request->validate([
            'email' => 'required|email'
        ]);

        $reference = $this->reference;

        $data = [
            'amount' => 100, // Replace with dynamic amount if needed
            'email' => $request->email,
            'tx_ref' => $reference,
            'currency' => "ETB",
            'callback_url' => route('callback', [$reference]),
            'first_name' => "Solomon",
            'last_name' => "Muhye",
            "customization" => [
                "title" => 'Medication',
                "description" => "Payment for my medications"
            ]
        ];

        $payment = Chapa::initializePayment($data);
        if ($payment['status'] !== 'success') {
            Log::error('Chapa Payment Initialization Failed', [
                    'reference' => $reference,
                    'request_data' => $data,
                    'response' => $payment
                ]);  
            return response()->json([
                'status' => 'error',
                'message' => 'Payment initialization failed'
            ], 500);
        }

        // Return checkout URL to frontend for redirect
        return response()->json([
            'status' => 'success',
            'checkout_url' => $payment['data']['checkout_url']
        ]);
    }

    /**
     * Handle Chapa callback
     */
    public function callback($reference)
    {
        $data = Chapa::verifyTransaction($reference);

        if ($data['status'] === 'success') {
            
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Payment successful'
            ]);
        }

        return response()->json([
            'status' => 'failed',
            'data' => $data,
            'message' => 'Payment failed or cancelled'
        ]);

    }
}
