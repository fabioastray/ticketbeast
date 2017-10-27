<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Carbon\Carbon;

use Tests\TestCase;
use App\Models\Concert;
use App\Models\Order;
use App\Exceptions\NotEnoughTicketsException;

class TicketTest extends TestCase
{
    use DatabaseMigrations;

    function test_a_ticket_can_be_released(){

        $ticketsQuantity = 1;
        $email = 'email1@example.com';
        $orderedTicketsQuantity = 1;
        $concert = factory(Concert::class)->create();
        $concert->addTickets($ticketsQuantity);

        $order = $concert->orderTickets($email, $orderedTicketsQuantity);
        $ticket = $order->tickets()->first();

        $this->assertEquals($order->id, $ticket->order_id);

        $ticket->release();

        $this->assertNull($ticket->fresh()->order_id);
    }
}