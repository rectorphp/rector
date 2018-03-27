<?php declare(strict_types=1);

$possibleAutoloadPaths = [
    // load from nearest vendor
    getcwd() . '/vendor/autoload.php',
];

foreach ($possibleAutoloadPaths as $possibleAutoloadPath) {
    if (is_file($possibleAutoloadPath)) {
        require_once $possibleAutoloadPath;
        return;
    }
}

die(sprintf(
    'Composer autoload.php was not found in paths "%s". Have you ran "composer update"?',
    implode('", "', $possibleAutoloadPaths)
));
