<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Concert;

class Ticket extends Model
{
    protected $guarded = [];

    // Attributes
    function getPriceAttribute(){
        return $this->concert->ticket_price;
    }

    // Relationships
    function concert(){
        return $this->belongsTo(Concert::class);
    }
    
    // Scopes
    function scopeAvailable($query){
        return $query->whereNull('order_id');
    }

    // Capabilities
    function release(){
        $this->update(['order_id' => null]);
    }
}
