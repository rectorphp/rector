<?php declare(strict_types=1);

$possibleAutoloadPaths = [
    // load from nearest vendor
    getcwd() . '/vendor/autoload.php',
    // repository
    __DIR__ . '/../vendor/autoload.php',
    // composer require
    __DIR__ . '/../../../../vendor/autoload.php',
];

foreach ($possibleAutoloadPaths as $possibleAutoloadPath) {
    if (is_file($possibleAutoloadPath)) {
        require_once $possibleAutoloadPath;

        break;
    }
}
