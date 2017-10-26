<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Concert;

class ConcertsController extends Controller
{
    function show($id){
        $concert = Concert::published('published_at')->findOrFail($id);
        return view('concerts.show')->with(compact('concert'));
    }
}
