<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Tests\TestCase;
use App\Models\Concert;
use Carbon\Carbon;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_can_get_formatted_date()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2017-12-01 8:00pm')
        ]);
        $this->assertEquals('December 1, 2017', $concert->formatted_date);
    }

    public function test_can_get_formatted_time()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2017-12-01 8:00pm')
        ]);
        $this->assertEquals('8:00pm', $concert->formatted_time);
    }

    public function test_can_get_formatted_ticket_price()
    {
        $concert = factory(Concert::class)->make([
            'ticket_price' => 3250
        ]);
        $this->assertEquals('32.50', $concert->formatted_ticket_price);
    }

    public function test_concerts_with_a_published_at_date_are_published()
    {
        $publishedConcertA = factory(Concert::class)->states('published')->create();
        $publishedConcertB = factory(Concert::class)->states('published')->create();
        $unpublishedConcert = factory(Concert::class)->states('unpublished')->create();

        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcert));
    }

    public function test_can_order_concert_tickets(){

        $email = 'jane@example.com';
        $ticketQuantity = 3;
        $concert = factory(Concert::class)->create();

        $order = $concert->orderTickets($email, $ticketQuantity);

        $this->assertEquals($email, $order->email);
        $this->assertEquals($ticketQuantity, $order->tickets()->count());
    }
}
