<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Concert;

class ConcertsController extends Controller
{
    function show($id){
        $concert = Concert::find($id);
        return view('concerts.show')->with('concert', $concert);
    }
}
