<?php

namespace App\Http\Controllers;
// This is your real test secret API key.
\Stripe\Stripe::setApiKey('sk_test_51HSjhvJcgXxPJBuLrRqGmCoDPMWkjGBi8UC61rUwnlvvjUA8UrH3FAPGoHRNpHkYLqCIwcA01pF3PSCoY8uXiV2w00qAG1Ih8z');

class OrdersController extends Controller
{
    public function index()
    {
        // TODO
        $userId = 1;

        $orders = \App\Order::retrieveAll($userId);

        return $orders->map(function ($order) {
            return [
              'id' => $order->id,
              'total_price' => $order->total_price,
              'currency' => $order->currency,
              'state' => $order->state,
              'created_at' => $order->created_at,
              'lines' => \App\Order::orderLines($order->id),
            ];
        });
    }

    public function initiate()
    {
        $userId = 1; // TODO

        \App\Order::stopAll($userId);

        return app('db')->transaction(function () use ($userId) {
            $orderId = \App\Order::initiate($userId);
            return app('db')
                ->table('order_lines')
                ->join('books', 'books.isbn', '=', 'order_lines.isbn')
                ->where('order_lines.order_id', $orderId)
                ->select('order_lines.*', 'books.title')
                ->get();
        });
    }
    public function confirm()
    {
        // TODO

    }

    public function listen_to_hook()
    {
        // If you are testing your webhook locally with the Stripe CLI you
        // can find the endpoint's secret by running `stripe listen`
        // Otherwise, find your endpoint's secret in your webhook settings in the Developer Dashboard
        $endpoint_secret = 'whsec_1IOgDUazJVRJsfke76nzM4jeoCYZQ35M';

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload

            http_response_code(400);
            exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object; // contains a StripePaymentIntent
                $this->handlePaymentIntentSucceeded($paymentIntent);
                break;
            case 'payment_method.attached':
                $paymentMethod = $event->data->object; // contains a StripePaymentMethod
                //$this->handlePaymentMethodAttached($paymentMethod);
                break;
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object; // contains a StripepaymentIntent
                $this->handlePaymentIntentFailed($paymentIntent);
                break;
            
            // ... handle other event types
            default:
                // Unexpected event type
                http_response_code(400);
                exit();
        }

        http_response_code(200);

    }

    public function handlePaymentIntentSucceeded($paymentIntent)
    {
        $orderId=$paymentIntent->charges->data[0]->metadata['orderId'];
        $order=\App\Order::retrieveById($orderId);

        if($order->state="initiatied"){
            if($paymentIntent->payment_status = "paid"){
                // set order to paid
                \App\Order::updateState($orderId, 'paid');
            }
            else{
                //do nothing
            }

        }
        //refund
        else if($order->state == 'stopped'){
            if($paymentIntent->payment_status = "paid"){
                $refund= \Stripe\Refund::create([
                    'payment_intent' => $paymentIntent->id
                ]);
                return ['status' => 'ERROR: REFUNDED'];
            }
        }
        

        

    }

    public function handlePaymentIntentFailed($paymentIntent)
    {
        $orderId=$paymentIntent->charges->data[0]->metadata['orderId'];

        \App\Order::updateState($orderId, 'failed');
    }

    public function create()
    {

            $orderId = isset($_GET['orderId']) ? $_GET['orderId'] : die();
            // retrieve JSON from POST body

            


            $order=\App\Order::retrieveById($orderId);

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $order->total_price,
                'currency' => 'eur',
                'metadata'=> [
                    'orderId' => $orderId
                ]
            ]);
            $output = [
              'clientSecret' => $paymentIntent->client_secret,
            ];
            
            return $output;
            //echo $output;
        }
         

    

   


}
