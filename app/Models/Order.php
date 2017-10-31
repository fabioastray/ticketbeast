<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Ticket;
use App\Models\Concert;

class Order extends Model
{
    protected $guarded = [];

    // Relationships
    function tickets(){
        return $this->hasMany(Ticket::class);
    }

    function concert(){
        return $this->hasOne(Concert::class, 'id', 'concert_id');
    }

    // Capabilities
    function cancel(){
        foreach ($this->tickets as $ticket) {
            $ticket->release();
        }

        $this->delete();
    }

    function ticketQuantity(){
        return $this->tickets->count();
    }

    function toArray(){
        return [
            'email' => $this->email,
            'ticket_quantity' => $this->ticketQuantity(),
            'amount' => $this->amount
        ];
    }

    static function forTickets($tickets, $email, $amount){
        $order = self::create([
            'email' => $email,
            'amount' => $amount
        ]);

        $tickets->each(function($ticket) use($order){
            $order->tickets()->save($ticket);
        });

        return $order;
    }
}
