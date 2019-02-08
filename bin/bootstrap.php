<?php declare(strict_types=1);

function includeProjectsAutoload(string $composerJsonPath, string $cwd): void
{
    $contents = file_get_contents($composerJsonPath);

    $composerSettings = json_decode($contents, true);
    if (! is_array($composerSettings)) {
        fwrite(STDERR, "Failed to load '${composerJsonPath}'\n");
        exit(1);
    }

    $vendorPath = $composerSettings['config']['vendor-dir'] ?? $cwd . '/vendor';
    if (! is_dir($vendorPath)) {
        fwrite(STDERR, "Please check if 'composer.phar install' was run already (expected to find '${vendorPath}')\n");
        exit(1);
    }

    /** @noinspection PhpIncludeInspection */
    require $vendorPath . '/autoload.php';
}

$cwd = getcwd();

$projectAutoload = $cwd . '/vendor/autoload.php';
if (is_file($projectAutoload)) {
    require $projectAutoload;
}

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

fwrite(
    STDERR,
    sprintf(
        'Composer autoload.php was not found in paths "%s". Have you ran "composer update"?',
        implode('", "', $possibleAutoloadPaths)
    )
);
exit(1);
