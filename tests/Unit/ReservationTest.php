<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Carbon\Carbon;

use Tests\TestCase;
use App\Models\Concert;
use App\Domains\Reservation;

class ReservationTest extends TestCase
{
    function test_calculating_the_total_cost(){

        $tickets = collect([
            (object)['price' => 1200],
            (object)['price' => 1200],
            (object)['price' => 1200],
        ]);
        $reservation = new Reservation($tickets);

        $this->assertEquals(3600, $reservation->totalCost());
    }
}