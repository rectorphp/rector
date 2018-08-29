<?php declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

function someFunction($someFile) {
    return Yaml::parse(file_get_contents($someFile));
}
