<?php
namespace App\Billing;

class FakePaymentGateway implements PaymentGateway{

    private $charges;

    function __construct(){
        $this->charges = collect();
    }

    function getValidTestToken(){
        return "valid-token";
    }
 
    function charge($amount, $token){
        if($token !== $this->getValidTestToken())
            throw new PaymentFailException;

        $this->charges->push($amount);
    }

    function totalCharges(){
        return $this->charges->sum();
    }
}