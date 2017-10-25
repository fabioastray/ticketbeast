<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Carbon\Carbon;

use App\Models\Concert;

class ViewConcertListingTest extends DuskTestCase
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
        // Define properties
        $concert_properties = [
            'title' => 'Mumford & Sons',
            'subtitle' => 'Presenting its latest album: Wilder Mind',
            'date' => Carbon::parse('December 13, 2017 8:00pm'),
            'ticket_price' => 3250,
            'venue' => 'Marlin Park Stadium',
            'venue_address' => '123 Example Street',
            'city' => 'Miami',
            'state' => 'FL',
            'zip' => '33015',
            'additional_information' => 'For tickets call (786) 216-2320'
        ];
        // Create a concert
        $concert = Concert::create([
            'title' => $concert_properties['title'],
            'subtitle' => $concert_properties['subtitle'],
            'date' => $concert_properties['date'],
            'ticket_price' => $concert_properties['ticket_price'],
            'venue' => $concert_properties['venue'],
            'venue_address' => $concert_properties['venue_address'],
            'city' => $concert_properties['city'],
            'state' => $concert_properties['state'],
            'zip' => $concert_properties['zip'],
            'additional_information' => $concert_properties['additional_information']
        ]);

    // Act
        // View the concert listing
        $this->browse(function (Browser $browser) use ($concert, $concert_properties) {
    // Assert
        // See the concert details
            $browser->visit("/concerts/{$concert->id}")
                    ->assertSee($concert_properties['title'])
                    ->assertSee($concert_properties['subtitle'])
                    ->assertSee($concert_properties['date'])
                    ->assertSee($concert_properties['ticket_price'])
                    ->assertSee($concert_properties['venue'])
                    ->assertSee($concert_properties['venue_address'])
                    ->assertSee($concert_properties['city'])
                    ->assertSee($concert_properties['state'])
                    ->assertSee($concert_properties['zip'])
                    ->assertSee($concert_properties['additional_information'])
            ;
        });

        // $this->get(env('APP_BASE_URL') . "/concerts/{$concert->id}");
    }
}