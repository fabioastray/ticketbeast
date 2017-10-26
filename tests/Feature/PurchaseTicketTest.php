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
    function test_customer_can_purchase_tickets(){
        // Arrange
        $ticketPrice = 3250;
        $ticketQuantity = 3;
        $expectedCharge = $ticketPrice * $ticketQuantity;

        $orderParams = [
            'email' => 'john@example.com',
            'ticket_quantity' => $ticketQuantity,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ];
        $concert = factory(Concert::class)->create(['ticket_price' => $ticketPrice]);
        
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
        $this->assertEquals($ticketQuantity, $order->tickets->count());
    }

    function test_email_is_required_to_purchase_tickets(){

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

    function test_quantity_is_required_to_purchase_tickets(){

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

    function test_quantity_must_be_at_least_1_to_purchase_tickets(){
        
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

    function test_payment_token_is_required_to_purchase_tickets(){
                
        $concert = factory(Concert::class)->create();
        $orderParams = [
            'email' => 'ana@example.com',
            'ticket_quantity' => 0
        ];

        $response = $this->orderTickets($concert, $orderParams);

        $response->assertJsonFragment(['payment_token']);
        $response->assertStatus(422);
    }
}
