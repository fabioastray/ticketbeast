<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

use App\Models\Concert;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Concert::class, function (Faker $faker) {
    return [
        'title' => 'Sample band',
        'subtitle' => 'Presenting its latest album: Random album name',
        'date' => Carbon::parse('+2 weeks'),
        'ticket_price' => 3250,
        'venue' => 'Sample Stadium',
        'venue_address' => '123 Example Street',
        'city' => 'Fakeville',
        'state' => 'FL',
        'zip' => '33015',
        'additional_information' => 'Some sample additional information'
    ];
});
