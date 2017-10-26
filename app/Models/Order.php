<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Ticket;

class Order extends Model
{
    protected $guarded = [];

    function tickets(){
        return $this->hasMany(Ticket::class);
    }
}
