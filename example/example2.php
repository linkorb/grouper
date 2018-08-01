<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'input.php';

$grouper = new \Grouper\Grouper();

// use https://github.com/fzaninotto/Faker to generate an array of 10.000 rows
// make sure to use a constant seed (https://github.com/fzaninotto/Faker#seeding-the-generator)
// Even with 10k rows, the grouper should still return in a reasonable time, i.e. <10 seconds
$faker = \Faker\Factory::create();
$faker->seed(7779311);
for ($i = 0; $i < 10000; $i++) {
    $input[] = [
        'id' => $i,
        'name' => $faker->name,
        'email' => $faker->email,
        'phone' => $faker->phoneNumber,
    ];
}

$start = \microtime(true);

$groups = $grouper->group($input, ['email', 'phone'], 'id');

$elapsed = \microtime(true) - $start;


echo sizeof($groups) . ' groups in ' . $elapsed . ' seconds' . PHP_EOL;
