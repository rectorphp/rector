<?php declare(strict_types=1);

$isDevRepository = file_exists(__DIR__ . '/../vendor/autoload.php');

// load the project with Prefixed Rector
if (defined('RECTOR_PREFIXED') && ! $isDevRepository) {
    $projectAutoload = getcwd() . '/vendor/autoload.php';
    if (is_file($projectAutoload)) {
        require_once $projectAutoload;
    }
}

$possibleAutoloadPaths = [
    // dev repository
    __DIR__ . '/../vendor/autoload.php',
    // composer require
    __DIR__ . '/../../../../vendor/autoload.php',
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
