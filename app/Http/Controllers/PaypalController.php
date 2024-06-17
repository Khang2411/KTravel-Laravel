<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Jobs\SendOrderMail;

class PaypalController extends Controller
{
    public function payment(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();

        $data = [
            'user_id' => $request->user_id,
            'host_id' => $request->host_id,
            'listing_id' => $request->listing_id,
            'user_name' => $request->user_name,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'adult' => $request->adult,
            'child' => $request->child,
            'status' => 'pending',
            'nights' => $request->nights,
            'price' => $request->price,
        ];

        $orderPayment = $provider->createOrder(
            [
                "intent" => "CAPTURE",
                "application_context" => [
                    "return_url" => route('checkout.paypal.success', $data),
                    "cancel_url" => route('checkout.paypal.cancel')
                ],
                "purchase_units" => [
                    [
                        "amount" => [
                            "currency_code" => "USD",
                            "value" => $request->price
                        ]
                    ]
                ]
            ]
        );


        if (isset($orderPayment['id']) && $orderPayment['id'] != null) {
            foreach ($orderPayment['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return response()->json(['redirect' => $link['href']]);
                }
            }
        } else {
            return redirect()->route('checkout.paypal.cancel');
        }
    }

    public function success(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        $orderPayment = $provider->capturePaymentOrder($request->token);

        $payment = Payment::create([
            'payment_id' => $orderPayment['id'],
            'payer_id' => $orderPayment['payer']['payer_id'],
            'payer_email' => $orderPayment['payer']['email_address'],
            'payer_name' => $orderPayment['payer']['name']['given_name'] . ' ' . $orderPayment['payer']['name']['surname'],
            'currency' => $orderPayment['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'],
            'payment_status' => $orderPayment['status'],
            'payment_method' => 'paypal'
        ]);

        $order = Order::create([
            'user_id' => $request->user_id,
            'host_id' => $request->host_id,
            'listing_id' => $request->listing_id,
            'payment_id' => $payment->id,
            'user_name' => $request->user_name,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'adult' => $request->adult,
            'child' => $request->child,
            'status' => 'pending',
            'nights' => $request->nights,
            'price' => $request->price,
        ]);

        if (isset($orderPayment['status']) && $orderPayment['status'] == "COMPLETED") {
            SendOrderMail::dispatch($order);
            return redirect(env('PAYPAL_RETURN'));
        } else {
            return redirect()->route('checkout.paypal.cancel');
        }
    }

    public function cancel()
    {
        return redirect(env('PAYPAL_CANCEL'));
    }
}
