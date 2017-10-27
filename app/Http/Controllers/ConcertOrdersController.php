<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Billing\PaymentGateway;
use App\Billing\PaymentFailException;
use App\Models\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Constants\HTTP_CODE;

class ConcertOrdersController extends Controller
{
    private $paymentGateway;

    function __construct(PaymentGateway $paymentGateway){
        $this->paymentGateway = $paymentGateway;
    }

    function store(Request $request, $concertId){

        $request->validate([
            'email' => 'required|email',
            'ticket_quantity' => 'required|numeric|min:1',
            'payment_token' => 'required'
        ]);

        $httpResponseCode = HTTP_CODE::CREATED;
        $deleteOrder = false;

        try{
            $concert = Concert::published()->findOrFail($concertId);

            $ticketQuantity = request('ticket_quantity');
            $token = request('payment_token');
            $email = request('email');
            $amount = $ticketQuantity * $concert->ticket_price;

            $order = $concert->orderTickets($email, $ticketQuantity);

            $this->paymentGateway->charge($amount, $token);
        }catch(PaymentFailException $e){
            $httpResponseCode = HTTP_CODE::UNPROCESSABLE_ENTITY;
            $deleteOrder = true;
        }catch(NotEnoughTicketsException $e){
            $httpResponseCode = HTTP_CODE::UNPROCESSABLE_ENTITY; 
            $deleteOrder = true;
        }

        if(!empty($order) && $deleteOrder)
            $order->cancel();

        return response()->json([], $httpResponseCode);
    }
}