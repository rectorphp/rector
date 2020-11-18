<?php

declare(strict_types=1);

$packageName = $argv[1];
$url = 'https://repo.packagist.org/p/' . $packageName . '.json';
$json = json_decode(file_get_contents($url), true);

$versions = $json['packages'][$packageName];

if (isset($versions['dev-master'])) {
    $version = 'dev-master';
} else {
    // Pick last version.
    $keys = array_keys($versions);
    $version = $keys[count($keys) - 1];
}

// Thanks to people excluding tests from dist packages,
// we're forced to clone source repos here.
$package = $versions[$version];
if ($package['source'] === null) {
    echo "Skipping due to missing source\n";
    return;
}
if ($package['source']['type'] !== 'git') {
    echo "Unexpected source type: ", $package['source']['type'], "\n";
    return;
}

$git = $package['source']['url'];

echo $git . PHP_EOL;
