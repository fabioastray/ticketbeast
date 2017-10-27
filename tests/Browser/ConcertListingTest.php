<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Carbon\Carbon;

use Tests\DuskTestCase;
use App\Models\Concert;
use App\Constants\HTTP_CODE;

class ViewConcertListingTest extends DuskTestCase
{
    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_user_can_view_a_published_concert_listings()
    {
        // Arrange
        $concert = factory(Concert::class)->states('published')->create([
            'date' => Carbon::parse('December 1, 2017 8:00pm'),
        ]);

        // Act
        $this->browse(function (Browser $browser) use ($concert) {
            // Assert
            $browser->visit("/concerts/{$concert->id}")
                    ->assertSee($concert->title)
                    ->assertSee($concert->subtitle)
                    ->assertSee('December 1, 2017')
                    ->assertSee('8:00pm')
                    ->assertSee('32.50')
                    ->assertSee($concert->venue)
                    ->assertSee($concert->venue_address)
                    ->assertSee($concert->city)
                    ->assertSee($concert->state)
                    ->assertSee($concert->zip)
                    ->assertSee($concert->additional_information);
        });
    }

    public function test_user_cannot_view_unpublished_concerts_listings()
    {
        // Arrange
        $concert = factory(Concert::class)->create([
            'published_at' => null
        ]);

        // Act
        $response = $this->get("/concerts/{$concert->id}");
        
        // Assert
        $response->assertStatus(HTTP_CODE::NOT_FOUND);
    }
}