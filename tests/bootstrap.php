<?php

declare(strict_types=1);

use Rector\Core\Stubs\StubLoader;
use Tracy\Debugger;

require_once __DIR__ . '/../vendor/autoload.php';

// @see https://github.com/phpstan/phpstan/issues/4541#issuecomment-779434916
require_once 'phar://vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionUnionType.php';
require_once 'phar://vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Attribute.php';

// silent deprecations, since we test them
error_reporting(E_ALL ^ E_DEPRECATED);

// performance boost
gc_disable();

// load stubs
$stubLoader = new StubLoader();
$stubLoader->loadStubs();

// make dump() useful and not nest infinity spam
Debugger::$maxDepth = 2;
