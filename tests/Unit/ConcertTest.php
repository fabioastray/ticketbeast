<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('2017-12-01 8:00pm')
        ]);
        $this->assertEquals('December 1, 2017', $concert->formatted_date);
    }

    public function test_can_get_formatted_time()
    {
        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('2017-12-01 8:00pm')
        ]);
        $this->assertEquals('8:00pm', $concert->formatted_time);
    }

    public function test_can_get_formatted_ticket_price()
    {
        $concert = factory(Concert::class)->create([
            'ticket_price' => 3250
        ]);
        $this->assertEquals('32.50', $concert->formatted_ticket_price);
    }
}
