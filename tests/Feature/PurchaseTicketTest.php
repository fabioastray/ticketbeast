<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Tests\TestCase;
use App\Models\Concert;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;
use App\Constants\HTTP_CODE;

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
        $concert = factory(Concert::class)->states('published')
                                          ->create(['ticket_price' => $ticketPrice])
                                          ->addTickets($ticketsQuantity);

        // Act
        $response = $this->orderTickets($concert, $orderParams);

        $this->assertTrue($concert->hasOrderFor($orderParams['email']));
        $order = $concert->getOrdersFor($orderParams['email'])->first();
        
        // Assert
        $response->assertStatus(HTTP_CODE::CREATED);
        $this->assertArraySubset(['email' => $orderParams['email'], 'ticket_quantity' => $ticketsQuantity, 'amount' => $expectedCharge], $response->original);
        $this->assertEquals($expectedCharge, $this->paymentGateway->totalCharges());
        $this->assertEquals($ticketsQuantity, $order->tickets->count());
    }

    function test_email_is_required_to_purchase_published_concert_tickets(){

        // Not working
        // $this->disableExceptionHandling();

        $ticketsQuantity = 3;
        
        $concert = factory(Concert::class)->create()
                                          ->addTickets($ticketsQuantity);
        $orderParams = [
            'ticket_quantity' => $ticketsQuantity,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ];

        $response = $this->orderTickets($concert, $orderParams);

        $response->assertJsonFragment(['email']);
        $response->assertStatus(HTTP_CODE::UNPROCESSABLE_ENTITY);
    }

    function test_quantity_is_required_to_purchase_published_concert_tickets(){

        $ticketsQuantity = 3;
        
        $concert = factory(Concert::class)->create()
                                          ->addTickets($ticketsQuantity);
        $orderParams = [
            'email' => 'ana@example.com',
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ];

        $response = $this->orderTickets($concert, $orderParams);

        $response->assertJsonFragment(['ticket_quantity']);
        $response->assertStatus(HTTP_CODE::UNPROCESSABLE_ENTITY);
    }

    function test_quantity_must_be_at_least_1_to_purchase_published_concert_tickets(){
        
        $ticketsQuantity = 0;
        
        $concert = factory(Concert::class)->create()->addTickets($ticketsQuantity);
        $orderParams = [
            'email' => 'ana@example.com',
            'ticket_quantity' => $ticketsQuantity,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ];

        $response = $this->orderTickets($concert, $orderParams);

        $response->assertJsonFragment(['ticket_quantity']);
        $response->assertStatus(HTTP_CODE::UNPROCESSABLE_ENTITY);
    }

    function test_payment_token_is_required_to_purchase_published_concert_tickets(){
         
        $ticketsQuantity = 0;
        $concert = factory(Concert::class)->create()->addTickets($ticketsQuantity);
        $orderParams = [
            'email' => 'ana@example.com',
            'ticket_quantity' => $ticketsQuantity
        ];

        $response = $this->orderTickets($concert, $orderParams);

        $response->assertJsonFragment(['payment_token']);
        $response->assertStatus(HTTP_CODE::UNPROCESSABLE_ENTITY);
    }

    function test_payment_token_is_valid_to_purchase_published_concert_tickets(){
        
        $ticketsQuantity = 3;
        $concert = factory(Concert::class)->states('published')
                                          ->create()
                                          ->addTickets($ticketsQuantity);
        $orderParams = [
            'email' => 'ana@example.com',
            'ticket_quantity' => $ticketsQuantity,
            'payment_token' => 'invalid-payment-token'
        ];

        $response = $this->orderTickets($concert, $orderParams);

        $response->assertStatus(HTTP_CODE::UNPROCESSABLE_ENTITY);
        $this->assertFalse($concert->hasOrderFor($orderParams['email']));
    }

    function test_cannot_purchase_tickets_to_an_unpublished_concert(){
        
        $ticketsQuantity = 3;
        $concert = factory(Concert::class)->states('unpublished')
                                          ->create()
                                          ->addTickets($ticketsQuantity);
        $orderParams = [
            'email' => 'ana@example.com',
            'ticket_quantity' => $ticketsQuantity,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ];

        $response = $this->orderTickets($concert, $orderParams);

        $response->assertStatus(HTTP_CODE::NOT_FOUND);
        $this->assertEquals(0, $concert->orders->count());
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }

    function test_cannot_purchase_more_tickets_than_remain(){
        
        $ticketsAmount = 50;
        $concert = factory(Concert::class)->states('published')
                                          ->create()
                                          ->addTickets($ticketsAmount);
        $orderParams = [
            'email' => 'ana@example.com',
            'ticket_quantity' => 51,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ];

        $response = $this->orderTickets($concert, $orderParams);

        $response->assertStatus(HTTP_CODE::UNPROCESSABLE_ENTITY);
        $this->assertFalse($concert->hasOrderFor($orderParams['email']));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals($ticketsAmount, $concert->ticketsRemaining());
    }

    function test_cannot_purchase_tickets_another_customer_is_already_trying_to_purchase(){
        
        $ticketsQuantity = 3;
        $orderParamsA = [
            'email' => 'personA@example.com',
            'ticket_quantity' => $ticketsQuantity,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ];
        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 1200])->addTickets(3);

        $this->paymentGateway->beforeFirstCharge(function($paymentGateway) use($concert){
            $orderParamsB = [
                'email' => 'personB@example.com',
                'ticket_quantity' => 1,
                'payment_token' => $this->paymentGateway->getValidTestToken()
            ];
            $response = $this->orderTickets($concert, $orderParamsB);

            $response->assertStatus(HTTP_CODE::UNPROCESSABLE_ENTITY);
            $this->assertFalse($concert->hasOrderFor($orderParamsB['email']));
            $this->assertEquals(0, $this->paymentGateway->totalCharges());
        });

        $response = $this->orderTickets($concert, $orderParamsA);

        $this->assertEquals(3600, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor($orderParamsA['email']));
        $this->assertEquals($ticketsQuantity, $concert->ordersFor($orderParamsA['email'])->first()->ticketQuantity());
    }
}