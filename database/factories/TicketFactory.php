<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

use App\Models\Ticket;
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

$factory->define(Ticket::class, function (Faker $faker) {
    return [
        'concert_id' => function(){
            return factory(Concert::class)->create()->id;
        }
    ];
});

// $factory->state(Concert::class, 'published', function(Faker $faker){
//     return [
//         'concert_id' => Carbon::parse()
//     ];
// });

// $factory->state(Concert::class, 'unpublished', function(Faker $faker){
//     return [
//         'published_at' => null
//     ];
// });