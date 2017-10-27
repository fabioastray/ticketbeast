<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = [];
    
    // Scopes
    function scopeAvailable($query){
        return $query->whereNull('order_id');
    }

    // Capabilities
    function release(){
        $this->update(['order_id' => null]);
    }
}
