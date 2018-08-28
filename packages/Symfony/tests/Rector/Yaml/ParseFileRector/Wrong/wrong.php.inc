<?php declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

$parsedFile = Yaml::parse('someFile.yml');
$parsedFile = Yaml::parse('someFile.yaml');
