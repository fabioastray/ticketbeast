<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Billing\PaymentGateway;
use App\Billing\PaymentFailedException;
use App\Models\Concert;

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

        $httpResponseCode = 201;

        try{
            $concert = Concert::find($concertId);

            $ticketQuantity = request('ticket_quantity');
            $token = request('payment_token');
            $email = request('email');

            $amount = $ticketQuantity * $concert->ticket_price;

            $this->paymentGateway->charge($amount, $token);

            $order = $concert->orderTickets($email, $ticketQuantity);
        }catch(PaymentFailedException $e){
            $httpResponseCode = 422;
        }

        return response()->json([], $httpResponseCode);
    }
}
