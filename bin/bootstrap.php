<?php

declare(strict_types=1);

function includeProjectsAutoload(string $composerJsonPath, string $cwd): void
{
    $contents = file_get_contents($composerJsonPath);

    $composerSettings = json_decode($contents, true);
    if (! is_array($composerSettings)) {
        die(sprintf('Failed to load "%s"', $composerJsonPath));
    }

    $vendorPath = $composerSettings['config']['vendor-dir'] ?? $cwd . '/vendor';
    if (! is_dir($vendorPath)) {
        die(sprintf('Please check if "composer install" was run already (expected to find "%s")', $vendorPath));
    }

    require $vendorPath . '/autoload.php';
}

$cwd = getcwd();

$projectAutoload = $cwd . '/vendor/autoload.php';
if (is_file($projectAutoload)) {
    require $projectAutoload;
}

// is autolaod successful?
if (class_exists('Rector\HttpKernel\RectorKernel')) {
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

$composerJsonPath = $cwd . '/composer.json';
if (file_exists($composerJsonPath)) {
    includeProjectsAutoload($composerJsonPath, $cwd);
    return;
}

die(
    sprintf(
        'Composer autoload.php was not found in paths "%s". Have you ran "composer update"?',
        implode('", "', $possibleAutoloadPaths)
    )
);
