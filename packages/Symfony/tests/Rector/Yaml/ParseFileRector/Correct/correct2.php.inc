<?php declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

$someFile = __DIR__ . '/someFile.yaml';
$parsedFile = Yaml::parse(file_get_contents($someFile));
