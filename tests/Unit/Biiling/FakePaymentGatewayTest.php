<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Billing\FakePaymentGateway;

class FakePaymentGatewayTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_charges_with_a_valid_payment_token_are_succesful()
    {
        $chargeAmount = 2500;
        $paymentGateway = new FakePaymentGateway;

        $paymentGateway->charge($chargeAmount, $paymentGateway->getValidTestToken());

        $this->assertEquals($chargeAmount, $paymentGateway->totalCharges());
    }
}
