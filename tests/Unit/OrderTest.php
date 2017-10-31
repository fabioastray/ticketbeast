<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Carbon\Carbon;

use Tests\TestCase;
use App\Models\Concert;
use App\Models\Order;
use App\Exceptions\NotEnoughTicketsException;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    function test_tickets_are_released_when_an_order_is_cancelled(){

        $ticketsQuantity = 10;
        $email = 'email1@example.com';
        $orderedTicketsQuantity = 5;
        $concert = factory(Concert::class)->create()
                                          ->addTickets($ticketsQuantity);

        $order = $concert->orderTickets($email, $orderedTicketsQuantity);
        $this->assertEquals($ticketsQuantity - $orderedTicketsQuantity, $concert->ticketsRemaining());
        
        $order->cancel();

        $this->assertNull($order->fresh());
        $this->assertEquals($ticketsQuantity, $concert->ticketsRemaining());
    }

    function test_amount_is_being_calculated_right(){

        $ticketsQuantity = 5;
        $ticketsPrice = 1200;
        $email = 'email1@example.com';
        $orderedTicketsQuantity = 5;
        $concert = factory(Concert::class)->create(['ticket_price' => $ticketsPrice])
                                            ->addTickets($ticketsQuantity);

        $order = $concert->orderTickets($email, $orderedTicketsQuantity);

        $this->assertEquals($order->amount, $orderedTicketsQuantity * $ticketsPrice);
    }

    function test_converting_to_an_array(){

        $ticketsQuantity = 5;
        $email = 'email1@example.com';
        $orderedTicketsQuantity = 5;
        $concert = factory(Concert::class)->create(['ticket_price' => $ticketsPrice = 1200])
                                          ->addTickets($ticketsQuantity);

        $order = $concert->orderTickets($email, $orderedTicketsQuantity);

        $result = $order->toArray();

        $this->assertEquals([
            'email' => $email,
            'ticket_quantity' => $orderedTicketsQuantity,
            'amount' => $orderedTicketsQuantity * $ticketsPrice
        ], $result);
    }

    function test_creating_an_order_from_tickets_email_and_amount(){

        $ticketsQuantity = 5;
        $email = 'email1@example.com';
        $orderedTicketsQuantity = 3;
        $concert = factory(Concert::class)->create()
                                          ->addTickets($ticketsQuantity);

        $this->assertEquals($ticketsQuantity, $concert->ticketsRemaining());

        $tickets = $concert->findTickets($orderedTicketsQuantity);
        $order = Order::forTickets($tickets, $email, 3600);

        $this->assertEquals($email, $order->email);
        $this->assertEquals($orderedTicketsQuantity, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals($ticketsQuantity - $orderedTicketsQuantity, $concert->ticketsRemaining());
    }
}