<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'input.php';
require_once 'output.php';

echo "=== Input data ===" . PHP_EOL;
print_r($input);

$grouper = new \Grouper\Grouper();

$groups = $grouper->group($input, ['email', 'phone'], 'id');
echo "=== Grouped rows ===" . PHP_EOL;

if ($groups == $output) {
    echo "Succes! :-)" . PHP_EOL;
} else {
    echo "Difference :-(" . PHP_EOL;
}
