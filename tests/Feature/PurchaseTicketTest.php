<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Tests\TestCase;
use App\Models\Concert;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;

class PurchaseTicketTest extends TestCase
{
    use DatabaseMigrations;

    protected $paymentGateway;

    protected function setUp(){
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    private function orderTickets($concert, $params){
        return $this->json('POST', "/concerts/{$concert->id}/orders", $params);
    }
    /**
     * A basic test example.
     *
     * @return void
     */
    function test_customer_can_purchase_published_concert_tickets(){
        // Arrange
        $ticketPrice = 3250;
        $ticketsQuantity = 3;
        $expectedCharge = $ticketPrice * $ticketsQuantity;

        $orderParams = [
            'email' => 'john@example.com',
            'ticket_quantity' => $ticketsQuantity,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ];
        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => $ticketPrice]);
        $concert->addTickets($ticketsQuantity);

        // Act
        $response = $this->orderTickets($concert, $orderParams);
        $order = $concert->orders()->where('email', $orderParams['email'])->first();
        
        // Assert
        $response->assertStatus(201);
        $this->assertEquals($expectedCharge, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->orders->contains(function($order) use($orderParams){
            return $order->email === $orderParams['email'];
        }));
        $this->assertNotNull($order);
        $this->assertEquals($ticketsQuantity, $order->tickets->count());
    }

    function test_email_is_required_to_purchase_published_concert_tickets(){

        // Not working
        // $this->disableExceptionHandling();

        $ticketQuantity = 3;
        
        $concert = factory(Concert::class)->create();
        $orderParams = [
            'ticket_quantity' => $ticketQuantity,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ];

        $response = $this->orderTickets($concert, $orderParams);

        $response->assertJsonFragment(['email']);
        $response->assertStatus(422);
    }

    function test_quantity_is_required_to_purchase_published_concert_tickets(){

        $ticketQuantity = 3;
        
        $concert = factory(Concert::class)->create();
        $orderParams = [
            'email' => 'ana@example.com',
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ];

        $response = $this->orderTickets($concert, $orderParams);

        $response->assertJsonFragment(['ticket_quantity']);
        $response->assertStatus(422);
    }

    function test_quantity_must_be_at_least_1_to_purchase_published_concert_tickets(){
        
        $ticketQuantity = 0;
        
        $concert = factory(Concert::class)->create();
        $orderParams = [
            'email' => 'ana@example.com',
            'ticket_quantity' => $ticketQuantity,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ];

        $response = $this->orderTickets($concert, $orderParams);

        $response->assertJsonFragment(['ticket_quantity']);
        $response->assertStatus(422);
    }

    function test_payment_token_is_required_to_purchase_published_concert_tickets(){
                
        $concert = factory(Concert::class)->create();
        $orderParams = [
            'email' => 'ana@example.com',
            'ticket_quantity' => 0
        ];

        $response = $this->orderTickets($concert, $orderParams);

        $response->assertJsonFragment(['payment_token']);
        $response->assertStatus(422);
    }

    function test_payment_token_is_valid_to_purchase_published_concert_tickets(){
        
        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(3);
        $orderParams = [
            'email' => 'ana@example.com',
            'ticket_quantity' => 3,
            'payment_token' => 'invalid-payment-token'
        ];

        $response = $this->orderTickets($concert, $orderParams);
        $order = $concert->orders()->where('email', $orderParams['email'])->first();

        $response->assertStatus(422);
        $this->assertNull($order);
    }

    function test_cannot_purchase_tickets_to_an_unpublished_concert(){
        
        $concert = factory(Concert::class)->states('unpublished')->create();
        $concert->addTickets(3);
        $orderParams = [
            'email' => 'ana@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ];

        $response = $this->orderTickets($concert, $orderParams);

        $response->assertStatus(404);
        $this->assertEquals(0, $concert->orders()->count());
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }

    function test_cannot_purchase_more_tickets_than_remain(){
        
        $ticketsAmount = 50;
        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets($ticketsAmount);
        $orderParams = [
            'email' => 'ana@example.com',
            'ticket_quantity' => 51,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ];

        $response = $this->orderTickets($concert, $orderParams);
        $order = $concert->orders()->where('email', $orderParams['email'])->first();

        $response->assertStatus(422);
        $this->assertNull($order);
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals($ticketsAmount, $concert->ticketsRemaining());
    }
}