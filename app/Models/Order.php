<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Ticket;

class Order extends Model
{
    protected $guarded = [];

    // Relationships
    function tickets(){
        return $this->hasMany(Ticket::class);
    }

    // Capabilities
    function cancel(){
        foreach ($this->tickets as $ticket) {
            $ticket->update(['order_id' => null]);
        }

        $this->delete();
    }
}
