<?php
namespace App\Billing;

interface PaymentGateway{

    function charge($amount, $token);
}