<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailException;

class FakePaymentGatewayTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    function test_charges_with_a_valid_payment_token_are_succesful(){
        $chargeAmount = 2500;
        $paymentGateway = new FakePaymentGateway;

        $paymentGateway->charge($chargeAmount, $paymentGateway->getValidTestToken());

        $this->assertEquals($chargeAmount, $paymentGateway->totalCharges());
    }

    function test_charges_with_an_invalid_payment_token_fail(){
        try{
            $chargeAmount = 2500;
            $paymentGateway = new FakePaymentGateway;

            $paymentGateway->charge($chargeAmount, 'invalid-payment-token');
        }catch(PaymentFailException $e){
            $this->assertTrue(true, true);
        }

        $this->fail();
    }
}
