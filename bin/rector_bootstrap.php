<?php declare(strict_types=1);

$possibleAutoloadPaths = [
    // repository
    __DIR__ . '/../vendor/autoload.php',
    // composer require
    __DIR__ . '/../../../../vendor/autoload.php',
    // load from nearest vendor
    getcwd() . '/vendor/autoload.php',
];

foreach ($possibleAutoloadPaths as $possibleAutoloadPath) {
    if (is_file($possibleAutoloadPath)) {
        require_once $possibleAutoloadPath;

        if(!class_exists('Rector\Parser\ParserFactory',true)){
            die('autoload broken');
        }

        return;
    }
}

die(sprintf(
    'Composer autoload.php was not found in paths "%s". Have you ran "composer update"?',
    implode('", "', $possibleAutoloadPaths)
));
