<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    protected $guarded = [];
    protected $dates = ['date'];

    function getFormattedDateAttribute(){
        return $this->date->format('F j, Y');
    }

    function getFormattedTimeAttribute(){
        return $this->date->format('g:ia');
    }

    function getFormattedTicketPriceAttribute(){
        return number_format($this->ticket_price / 100, 2);
    }
}