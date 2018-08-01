<?php declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

$yaml = [];

Symfony\Component\Yaml\Yaml::parse($yaml, Symfony\Component\Yaml\Yaml::PARSE_KEYS_AS_STRINGS);

Symfony\Component\Yaml\Yaml::parse($yaml, Yaml::PARSE_KEYS_AS_STRINGS);

Symfony\Component\Yaml\Yaml::parse($yaml, Symfony\Component\Yaml\Yaml::PARSE_CONSTANT);

Yaml::parse($yaml, Yaml::PARSE_KEYS_AS_STRINGS);
