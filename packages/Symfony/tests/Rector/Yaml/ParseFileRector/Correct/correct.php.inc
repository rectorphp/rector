<?php declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

$parsedFile = Yaml::parse(file_get_contents('someFile.yml'));
$parsedFile = Yaml::parse(file_get_contents('someFile.yaml'));
$parsedFile = Yaml::parse(file_get_contents("someFile.yaml"));
$parsedFile = Yaml::parse(file_get_contents($directory . "someFile.yaml"));
$parsedFile = Yaml::parse(file_get_contents(__DIR__ . 'someFile.yaml'));

$someFile = __DIR__ . 'someFile.yaml';
$parsedFile = Yaml::parse($someFile);
