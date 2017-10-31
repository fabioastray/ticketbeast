<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Billing\PaymentGateway;
use App\Billing\PaymentFailException;
use App\Models\Concert;
use App\Models\Order;
use App\Exceptions\NotEnoughTicketsException;
use App\Constants\HTTP_CODE;
use App\Domains\Reservation;

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
        $ticketQuantity = request('ticket_quantity');
        $token = request('payment_token');
        $email = request('email');

        $concert = Concert::published()->findOrFail($concertId);

        try{
            $tickets = $concert->findTickets($ticketQuantity);
            $reservation = new Reservation($tickets); 

            $this->paymentGateway->charge($reservation->totalCost(), $token);

            $order = Order::forTickets($tickets, $email, $reservation->totalCost());            
        }catch(PaymentFailException $e){
            $httpResponseCode = HTTP_CODE::UNPROCESSABLE_ENTITY;
        }catch(NotEnoughTicketsException $e){
            $httpResponseCode = HTTP_CODE::UNPROCESSABLE_ENTITY; 
        }

        return response()->json(!empty($order) ? $order->toArray() : [], $httpResponseCode);
    }
}