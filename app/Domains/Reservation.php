<?php

namespace App\Domains;

use Illuminate\Support\Collection;

class Reservation{

    private $tickets;

    function __construct(Collection $tickets){
        $this->tickets = $tickets;
    }

    function totalCost(){
        return $this->tickets->sum('price');
    }
}