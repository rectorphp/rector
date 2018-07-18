<?php declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

Yaml::dump(array('foo' => new A(), 'bar' => 1), 0, 0, \Symfony\Component\Yaml\Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE);

Yaml::dump(array('foo' => new A(), 'bar' => 1), 0, 0, \Symfony\Component\Yaml\Yaml::DUMP_OBJECT);
