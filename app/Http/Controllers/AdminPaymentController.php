<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class AdminPaymentController extends Controller
{
    function list()
    {
        $dateNow = now()->toDateString();
        $list_action = ['delete' => 'Xóa tạm thời'];
        switch (request()->status) {
            case ('paid'):
                $orders = Order::where('status', 'completed')->with([
                    'listing' =>  function ($query) {
                        return $query->with('images');
                    }
                ])->with('user')->with('payment')
                    ->when(request()->search, function ($q) {
                        return $q->where('user_name', 'LIKE', '%' . request()->search . '%');
                    })->orderBy("id", "DESC")->paginate(10);

                $orders->appends(['status' => 'paid', 'search' => request()->search]); // khi nhấn qua page 2 vẫn ở status=trash
                $list_action = ['restore' => 'Khôi phục', 'force_delete' => 'Xóa vĩnh viễn'];
                break;
            default:
                $orders = Order::where('check_in', '<', $dateNow)->where('status', 'pending')->with([
                    'listing' =>  function ($query) {
                        return $query->with('images');
                    }
                ])->with('user')->with('payment')
                    ->when(request()->search, function ($q) {
                        return $q->where('user_name', 'LIKE', '%' . request()->search . '%');
                    })->orderBy("id", "DESC")->paginate(10);

                $orders->appends(['status' => 'unpaid', 'search' => request()->search]);
                break;
        }
        $count_order_unpaid = Order::where('status', 'pending')->count();
        $count_order_paid = Order::where('status',  'completed')->count();
        $count = [$count_order_unpaid, $count_order_paid];

        return Inertia::render('Payment/PayList', ['orders' => $orders, 'list_action' => $list_action, 'count' => $count]);
    }

    function transfer(Request $request)
    {
        $payerEmail = $request->payerEmail;
        $price = $request->price;
        $orderID = $request->orderID;

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();

        $data = [
            'order_id' => $orderID,
        ];

        $orderPayment = $provider->createOrder(
            [
                "intent" => "CAPTURE",
                "application_context" => [
                    "return_url" => route('admin.payment.success', $data),
                    "cancel_url" => route('admin.payment.cancel')
                ],
                "purchase_units" => [
                    [
                        "amount" => [
                            "currency_code" => "USD",
                            "value" =>   $price - ($price * 0.2)
                        ],
                        "payee" => [
                            "email_address" => $payerEmail,
                        ],
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
            return redirect()->route('admin.payment.cancel');
        }
    }
    function success(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        $orderPayment = $provider->capturePaymentOrder($request->token);

        if (isset($orderPayment['status']) && $orderPayment['status'] == "COMPLETED") {
            $order = Order::find($request->order_id);
            $order->status = "completed";
            $order->save();
            return "<script>window.opener.postMessage('send-info'); window.close();</script>";
        } else {
            return redirect()->route('admin.payment.cancel');
        }
    }
    public function cancel()
    {
        return "Something wrong happened";
    }
}
