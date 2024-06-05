<?php

namespace App\Http\Controllers;
use App\Models\Order;
use Srmklive\PayPal\Services\PayPal;
use Srmklive\PayPal\Services\ExpressCheckout;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use DB;
class PayPalController extends Controller
{
    public function payment()
    {
        $cart = Cart::where('user_id',auth()->user()->id)->where('order_id',null)->get()->toArray();

        $data = [];

        $provider = new PayPal;
        $provider->setApiCredentials(config('paypal'));
        $payPalToken = $provider->getAccessToken();

        $response = $provider->createOrder([
            'intent' => "CAPTURE",
            "application_context" => [
                "return_url" => route('payment.success'),
                "cancel_url" => route('payment.cancel'),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        'currency_code' => 'USD',
                        'value' => Order::find(session()->get('id'))->total_amount,
                    ]
                ]
            ]
        ]);

        foreach ($response['links'] as $links) {
            if ($links['rel'] == 'approve') {
                return redirect()->away($links['href']);
            }
        }
                foreach($data['items'] as $item) {
            $total += $item['price']*$item['qty'];
        }

        $data['total'] = $total;
        if(session('coupon')){
            $data['shipping_discount'] = session('coupon')['value'];
        }
        Cart::where('user_id', auth()->user()->id)->where('order_id', null)->update(['order_id' => session()->get('id')]);

        // return session()->get('id');
        $provider = new ExpressCheckout;

        try {
            $response = $provider->setExpressCheckout($data);
        } catch (\Throwable $e) {
            report($e);

            dd($e->getMessage());
        }
        dd($response['paypal_link']);
        return redirect($response['paypal_link']);
    }

    /**
     * Responds with a welcome message with instructions
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel()
    {
        redirect()->route('home');
    }

    /**
     * Responds with a welcome message with instructions
     *
     */
    public function success(Request $request)
    {
        $provider = new PayPal;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);
        Cart::where('user_id', auth()->user()->id)->where('order_id', null)->update(['order_id' => session()->get('id')]);

        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            request()->session()->flash('success','You successfully pay from Paypal! Thank You');
        } else {
            request()->session()->flash('error','Something went wrong please try again!!!');
        }
        return redirect()->route('home');
    }
}
