<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Order;

class Concert extends Model
{
    protected $guarded = [];
    protected $dates = ['date'];

    // Attribute
    function getFormattedDateAttribute(){
        return $this->date->format('F j, Y');
    }

    function getFormattedTimeAttribute(){
        return $this->date->format('g:ia');
    }

    function getFormattedTicketPriceAttribute(){
        return number_format($this->ticket_price / 100, 2);
    }

    // Relationships
    function orders(){
        return $this->hasMany(Order::class);
    }

    // Scopes
    function scopePublished($query){
        return $query->whereNotNull('published_at');
    }

    // 
    function orderTickets($email, $ticketQuantity){

        $order = $this->orders()->create(['email' => $email]);
        
        foreach (range(1, $ticketQuantity) as $i) {
            $order->tickets()->create([]);
        }

        return $order;
    }
}