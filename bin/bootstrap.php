<?php declare(strict_types=1);

$projectAutoload = getcwd() . '/vendor/autoload.php';
if (is_file($projectAutoload)) {
    require $projectAutoload;
}

if (class_exists('Rector\DependencyInjection\RectorKernel')) {
    return;
}

$possibleAutoloadPaths = [
    // dev repository or prefixed rector
    __DIR__ . '/../vendor/autoload.php',
    // composer require
    __DIR__ . '/../../../../vendor/autoload.php',
];

foreach ($possibleAutoloadPaths as $possibleAutoloadPath) {
    if (is_file($possibleAutoloadPath)) {
        require $possibleAutoloadPath;
        return;
    }
}

die(sprintf(
    'Composer autoload.php was not found in paths "%s". Have you ran "composer update"?',
    implode('", "', $possibleAutoloadPaths)
));
