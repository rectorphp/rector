<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Argument\ArgumentDefaultValueReplacerRector\Wrong;

use Symfony\Component\Yaml\Yaml;

Yaml::parse('...', \Symfony\Component\Yaml\Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);

Yaml::parse('...', 0);
