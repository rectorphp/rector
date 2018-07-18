<?php declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

Yaml::dump(array('foo' => new A(), 'bar' => 1), 0, 0, true);

Yaml::dump(array('foo' => new A(), 'bar' => 1), 0, 0, false, true);
