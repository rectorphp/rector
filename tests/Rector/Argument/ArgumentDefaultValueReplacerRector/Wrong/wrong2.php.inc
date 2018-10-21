<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Argument\ArgumentDefaultValueReplacerRector\Wrong;

use Symfony\Component\Yaml\Yaml;

Yaml::parse('...', true);

Yaml::parse('...', false);
