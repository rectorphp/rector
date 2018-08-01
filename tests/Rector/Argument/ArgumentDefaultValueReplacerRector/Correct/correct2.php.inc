<?php declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

Yaml::parse('...', \Symfony\Component\Yaml\Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);

Yaml::parse('...', 0);
