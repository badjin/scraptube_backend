<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\SongRequest;
use Faker\Generator as Faker;

$factory->define(SongRequest::class, function (Faker $faker) {
    $user_id = mt_rand(1, 104);
    $min = strtotime('2020-01-01');
    $max = strtotime('2020-07-01');
    // Generate random number using above bounds
    $val = rand($min, $max);
    return [
        'user_id' => $user_id,
        'title' => $faker->text(mt_rand(20, 30)),
        'description' => $faker->text(mt_rand(200, 400)),
        'created_at' =>date('Y-m-d H:i:s', $val),
        'updated_at' =>date('Y-m-d H:i:s', $val),
    ];
});
