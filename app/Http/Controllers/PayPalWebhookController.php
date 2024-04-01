<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PayPalWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $webhookData = $request->all();
        $userid = Auth::user()->id;


        if(isset($webhookData['event_type'])){
            $eventType = $webhookData['event_type'];
            
            if($eventType === 'PAYMENT.CAPTURE.COMPLETED'){
                $paymentData = $webhookData['resource'];

                $payment = Payment::create([
                    'user_id' => $userid,
                    'product_solution_id' => "",
                    'payment_method' => 'paypal',
                    'event_type' => $eventType,
                    'payment_amount' => $paymentData['amount']['value'],
                    'payment_currency' => $paymentData['amount']['currency_code'],
                    'transaction_id' => $paymentData['id'],
                    'status' => $paymentData['status'],
                    'summary' => $webhookData['summary']
                ]);
                //----------------------------------------------------------------
                $jsonData = json_encode($webhookData, JSON_PRETTY_PRINT);
                $SavePath = storage_path('PaymentDetails');
                if(!file_exists($SavePath)){
                    mkdir($SavePath, 0775, true);
                }

                $filePath = $SavePath . '/' .$paymentData['id'] . '-PAYMENT_COMPLETED.json';
                file_put_contents($filePath, $jsonData);

            }elseif($eventType === 'CHECKOUT.ORDER.APPROVED'){
                
            }elseif($eventType === 'PAYMENT.CAPTURE.REFUNDED'){
                $paymentData = $webhookData['resource'];
                $refundedData = $paymentData['seller_payable_breakdown']['total_refunded_amount'];

                $payment = Payment::create([
                    'user_id' => $userid,
                    'product_solution_id' => "",
                    'payment_method' => 'paypal',
                    'event_type' => $eventType,
                    'payment_amount' => $refundedData['value'],
                    'payment_currency' => $refundedData['currency_code'],
                    'transaction_id' => $paymentData['id'],
                    'status' => $paymentData['status'],
                    'summary' => $webhookData['summary']
                ]);
                //----------------------------------------------------------------
                $jsonData = json_encode($webhookData, JSON_PRETTY_PRINT);
                $SavePath = storage_path('PaymentDetails');
                if(!file_exists($SavePath)){
                    mkdir($SavePath, 0775, true);
                }

                $filePath = $SavePath . '/' .$paymentData['id'] . '-PAYMENT_REFUNDED.json';
                file_put_contents($filePath, $jsonData);

            }elseif($eventType === 'CUSTOMER.DISPUTE.CREATED'){
                $customerData = $webhookData['resource'];
                $buyerData = $customerData['offer']['buyer_requested_amount'];
                $transaction_id = $customerData['disputed_transactions'][0]['seller_transaction_id'];

                $customer = Payment::create([
                    'user_id' => $userid,
                    'product_solution_id' => "",
                    'payment_method' => 'paypal',
                    'event_type' => $eventType,
                    'payment_amount' => $buyerData['value'],
                    'payment_currency' => $buyerData['currency_code'],
                    'transaction_id' => $transaction_id,
                    'status' => $customerData['status'],
                    'summary' => $webhookData['summary']
                ]);
                //----------------------------------------------------------------
                $jsonData = json_encode($webhookData, JSON_PRETTY_PRINT);
                $SavePath = storage_path('CustomerDispute');
                if(!file_exists($SavePath)){
                    mkdir($SavePath, 0775, true);
                }

                $filePath = $SavePath . '/' .$transaction_id . '-CREATED.json';
                file_put_contents($filePath, $jsonData);

            }elseif($eventType === 'CUSTOMER.DISPUTE.UPDATED'){
                $customerData = $webhookData['resource'];
                $buyerData = $customerData['offer']['buyer_requested_amount'];
                $transaction_id = $customerData['disputed_transactions'][0]['seller_transaction_id'];

                $customer = Payment::create([
                    'user_id' => $userid,
                    'product_solution_id' => "",
                    'payment_method' => 'paypal',
                    'event_type' => $eventType,
                    'payment_amount' => $buyerData['value'],
                    'payment_currency' => $buyerData['currency_code'],
                    'transaction_id' => $transaction_id,
                    'status' => $customerData['status'],
                    'summary' => $webhookData['summary']
                ]);
                //----------------------------------------------------------------
                $jsonData = json_encode($webhookData, JSON_PRETTY_PRINT);
                $SavePath = storage_path('CustomerDispute');
                if(!file_exists($SavePath)){
                    mkdir($SavePath, 0777, true);
                }

                $filePath = $SavePath . '/' .$transaction_id . '-UPDATED.json';
                file_put_contents($filePath, $jsonData);

            }elseif($eventType === 'CUSTOMER.DISPUTE.RESOLVED'){
                $customerData = $webhookData['resource'];
                $refundedData = $customerData['dispute_outcome']['amount_refunded'];
                $transaction_id = $customerData['disputed_transactions'][0]['seller_transaction_id'];

                $customer = Payment::create([
                    'user_id' => $userid,
                    'product_solution_id' => "",
                    'payment_method' =>'paypal',
                    'event_type' => $eventType,
                    'payment_amount' => $refundedData['value'],
                    'payment_currency' => $refundedData['currency_code'],
                    'transaction_id' => $transaction_id,
                    'status' => $customerData['status'],
                    'summary' => $webhookData['summary']
                ]);
                //----------------------------------------------------------------
                $jsonData = json_encode($webhookData, JSON_PRETTY_PRINT);
                $SavePath = storage_path('CustomerDispute');
                if(!file_exists($SavePath)){
                    mkdir($SavePath, 0777, true);
                }

                $filePath = $SavePath . '/' .$transaction_id . '-RESOLVED.json';
                file_put_contents($filePath, $jsonData);

            }

            Log::info($webhookData);

            return response()->json(['success' => true]);

        } else{
            Log::info('Webhook Fail');
        }

    }
}
