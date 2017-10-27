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
        $concert = factory(Concert::class)->create();
        $concert->addTickets($ticketsQuantity);

        $order = $concert->orderTickets($email, $orderedTicketsQuantity);
        $this->assertEquals($ticketsQuantity - $orderedTicketsQuantity, $concert->ticketsRemaining());
        
        $order->cancel();

        $this->assertNull(Order::find($order->id));
        $this->assertEquals($ticketsQuantity, $concert->ticketsRemaining());
    }
}