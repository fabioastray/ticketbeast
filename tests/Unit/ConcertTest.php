<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Carbon\Carbon;

use Tests\TestCase;
use App\Models\Concert;
use App\Exceptions\NotEnoughTicketsException;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    function test_can_get_formatted_date(){
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2017-12-01 8:00pm')
        ]);
        $this->assertEquals('December 1, 2017', $concert->formatted_date);
    }

    function test_can_get_formatted_time(){
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2017-12-01 8:00pm')
        ]);
        $this->assertEquals('8:00pm', $concert->formatted_time);
    }

    function test_can_get_formatted_ticket_price(){
        $concert = factory(Concert::class)->make([
            'ticket_price' => 3250
        ]);
        $this->assertEquals('32.50', $concert->formatted_ticket_price);
    }

    function test_concerts_with_a_published_at_date_are_published(){
        $publishedConcertA = factory(Concert::class)->states('published')->create();
        $publishedConcertB = factory(Concert::class)->states('published')->create();
        $unpublishedConcert = factory(Concert::class)->states('unpublished')->create();

        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcert));
    }

    function test_can_order_concert_tickets(){

        $email = 'jane@example.com';
        $ticketsQuantity = 3;
        $concert = factory(Concert::class)->create();
        $concert->addTickets($ticketsQuantity);

        $order = $concert->orderTickets($email, $ticketsQuantity);

        $this->assertEquals($email, $order->email);
        $this->assertEquals($ticketsQuantity, $order->tickets()->count());
    }

    function test_can_add_concert_tickets(){

        $ticketsQuantity = 50;
        $concert = factory(Concert::class)->create();

        $concert->addTickets($ticketsQuantity);

        $this->assertEquals($ticketsQuantity, $concert->ticketsRemaining());
    }

    function test_tickets_remaining_does_not_include_tickets_associated_with_an_order(){

        $ticketsQuantity = 50;
        $email = 'jane@example.com';
        $orderedTicketsQuantity = 30;
        $concert = factory(Concert::class)->create();

        $concert->addTickets($ticketsQuantity);
        $concert->orderTickets($email, $orderedTicketsQuantity);

        $this->assertEquals($ticketsQuantity - $orderedTicketsQuantity, $concert->ticketsRemaining());
    }

    function test_trying_to_purchase_more_tickets_than_remain_throws_an_exception(){

        $ticketsQuantity = 30;
        $email = 'jane@example.com';
        $orderedTicketsQuantity = 31;
        $concert = factory(Concert::class)->create();

        $concert->addTickets($ticketsQuantity);
        try{
            $concert->orderTickets($email, $orderedTicketsQuantity);
            $this->fail('Order succeded even though there were not enough tickets remaining');
        }catch(NotEnoughTicketsException $e){
            $order = $concert->orders()->where('email', $email)->first();
            $this->assertNull($order);
            $this->assertEquals($ticketsQuantity, $concert->ticketsRemaining());
        }
    }

    function test_cannot_order_tickets_that_have_already_been_purchased(){

        $ticketsQuantity = 10;
        $email1 = 'email1@example.com';
        $email2 = 'email2@example.com';
        $orderedTicketsQuantity1 = 8;
        $orderedTicketsQuantity2 = 3;
        $concert = factory(Concert::class)->create();

        $concert->addTickets($ticketsQuantity);
        $concert->orderTickets($email1, $orderedTicketsQuantity1);

        try{
            $concert->orderTickets($email2, $orderedTicketsQuantity2);

            $this->fail('Order succeded even though there were not enough tickets remaining');
        }catch(NotEnoughTicketsException $e){
            $order = $concert->orders()->where('email', $email2)->first();

            $this->assertNull($order);
            $this->assertEquals($ticketsQuantity - $orderedTicketsQuantity1, $concert->ticketsRemaining());
        }
    }

    function test_can_decide_if_has_order_for(){

        $ticketsQuantity = 1;
        $orderedTicketsQuantity = 1;
        $email1 = 'email1@example.com';
        $email2 = 'email2@example.com';
        $concert = factory(Concert::class)->create()
                                          ->addTickets($ticketsQuantity);
        $concert->orderTickets($email1, $orderedTicketsQuantity);

        $this->assertTrue($concert->hasOrderFor($email1));
        $this->assertFalse($concert->hasOrderFor($email2));
    }

    function test_can_get_an_order_for(){

        $ticketsQuantity = 1;
        $orderedTicketsQuantity = 1;
        $email1 = 'email1@example.com';
        $email2 = 'email2@example.com';
        $concert = factory(Concert::class)->create()
                                            ->addTickets($ticketsQuantity);
        $concert->orderTickets($email1, $orderedTicketsQuantity);

        $this->assertNotNull($concert->getOrdersFor($email1)->first());
        $this->assertNull($concert->getOrdersFor($email2)->first());
    }

    function test_can_reserve_available_tickets(){
        $concert = factory(Concert::class)->create()->addTickets(3);

        $this->assertEquals(3, $concert->ticketsRemaining());

        $reservedTickets = $concert->reserveTickets(2);

        $this->assertCount(2, $reservedTickets);
        $this->assertEquals(1, $concert->ticketsRemaining());
    }
}