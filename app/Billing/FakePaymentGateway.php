<?php
namespace App\Billing;

class FakePaymentGateway implements PaymentGateway{

    private $charges;
    private $beforeFirstChargeCallback;

    function __construct(){
        $this->charges = collect();
    }

    function getValidTestToken(){
        return "valid-token";
    }
 
    function charge($amount, $token){
        if($this->beforeFirstChargeCallback !== null){
            $callback = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;

            $callback($this);
        }

        if($token !== $this->getValidTestToken())
            throw new PaymentFailException;

        $this->charges->push($amount);
    }

    function totalCharges(){
        return $this->charges->sum();
    }

    function beforeFirstCharge($callback){
        $this->beforeFirstChargeCallback = $callback;
    }
}