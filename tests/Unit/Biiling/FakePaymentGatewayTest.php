<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailException;

class FakePaymentGatewayTest extends TestCase
{
    function test_charges_with_a_valid_payment_token_are_succesful(){
        $chargeAmount = 2500;
        $paymentGateway = new FakePaymentGateway;

        $paymentGateway->charge($chargeAmount, $paymentGateway->getValidTestToken());

        $this->assertEquals($chargeAmount, $paymentGateway->totalCharges());
    }

    function test_charges_with_an_invalid_payment_token_fail(){
        try{
            $paymentGateway = new FakePaymentGateway;
            $paymentGateway->charge(2500, 'invalid-payment-token');

            $this->fail();
        }catch(PaymentFailException $e){
            $this->assertTrue(true, true);
        }
    }

    function test_running_a_hook_before_the_first_charge(){
        $paymentGateway = new FakePaymentGateway;
        $timesCallbackRan = 0;

        $paymentGateway->beforeFirstCharge(function($paymentGateway) use(&$timesCallbackRan){
            $timesCallbackRan++;
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
            $this->assertEquals(2500, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        $this->assertEquals(1, $timesCallbackRan);
        $this->assertEquals(5000, $paymentGateway->totalCharges());
    }
}
