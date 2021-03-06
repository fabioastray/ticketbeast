<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Exceptions\NotEnoughTicketsException;
use App\Models\Order;

class Concert extends Model
{
    protected $guarded = [];
    protected $dates = ['date'];

    // Attributes
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
        return $this->belongsToMany(Order::class, 'tickets');
    }

    function tickets(){
        return $this->hasMany(Ticket::class);
    }

    // Scopes
    function scopePublished($query){
        return $query->whereNotNull('published_at');
    }

    // Capabilities
    function orderTickets($email, $ticketQuantity){

        $tickets = $this->findTickets($ticketQuantity);

        return $this->createOrder($email, $tickets);
    }

    function findTickets($ticketQuantity){
        $tickets = $this->tickets()->available()->take($ticketQuantity)->get();
        
        if($tickets->count() < $ticketQuantity)
            throw new NotEnoughTicketsException;

        return $tickets; 
    }

    function createOrder($email, $tickets){
        return Order::forTickets($tickets, $email, $tickets->sum('price'));
    }

    function addTickets($amount){
        foreach (range(1, $amount) as $i) {
            $this->tickets()->create([]);
        }

        return $this;
    }

    function ticketsRemaining(){
        return $this->tickets()->available()->count();
    }

    function hasOrderFor($customerEmail){
        return $this->orders()->where('email', $customerEmail)->count() > 0;
    }

    function getOrdersFor($customerEmail){
        return $this->orders()->where('email', $customerEmail)->get();
    }

    function reserveTickets($ticketQuantity){
       return $this->findTickets($ticketQuantity);
    }
}