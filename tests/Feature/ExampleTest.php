<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Chrome;
use Carbon\Carbon;

use App\Concert;

class ViewConcertListingTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_user_can_view_a_concert_listing()
    {
    // Arrange
        // Create a concert
        $concert = Concert::create([
            'title' => $title = 'Mumford & Sons',
            'subtitle' => $subtitle = 'Presenting its latest album: Wilder Mind',
            'date' => $date = Carbon::parse('December 13, 2017 8:00pm'),
            'ticket_price' => $ticket_price = 3250,
            'venue' => $venue = 'Marlin Park Stadium',
            'venue_address' => $venue_address = '123 Example Street',
            'city' => $city = 'Miami',
            'state' => $state = 'FL',
            'zip' => $zip = '33015',
            'additional_information' => $additional_information = 'For tickets call (786) 216-2320'
        ]);

    // Act
        //View the concert listing
        $this->browse(function ($browser) use ($concert) {
            $browser->visit("/concerts/{$concert->id}");
        });

    // Assert
        // See the concert details
        $this->see($title);
        $this->see($subtitle);
        $this->see($date);
        $this->see($ticket_price);
        $this->see($venue);
        $this->see($venue_address);
        $this->see($city);
        $this->see($state);
        $this->see($zip);
        $this->see($additional_information);
    }
}
