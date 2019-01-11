<?php declare(strict_types=1);

$cwd = getcwd();

$projectAutoload = $cwd . '/vendor/autoload.php';
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

$composer_json_path = $cwd . '/composer.json';
if (file_exists($composer_json_path)) {
    $contents = file_get_contents($composer_json_path);

    $composer_settings = json_decode($contents, true);
    if (!is_array($composer_settings)) {
        fwrite(STDERR, "Failed to load '$composer_json_path'\n");
        exit(1);
    }

    $vendor_path = $composer_settings['config']['vendor-dir'] ?? $cwd . '/vendor';
    if (!is_dir($vendor_path)) {
        fwrite(STDERR, "Please check if 'composer.phar install' was run already (expected to find '$vendor_path')\n");
        exit(1);
    }

    /** @noinspection PhpIncludeInspection */
    require $vendor_path . '/autoload.php';
    return;
}

fwrite(STDERR, sprintf(
    'Composer autoload.php was not found in paths "%s". Have you ran "composer update"?',
    implode('", "', $possibleAutoloadPaths)
));
exit(1);
