<?php

declare(strict_types=1);

use Rector\Core\Stubs\StubLoader;

require_once __DIR__ . '/../vendor/autoload.php';

require_once 'phar://vendor/phpstan/phpstan/phpstan.phar/vendor/jetbrains/phpstorm-stubs/PhpStormStubsMap.php';

// silent deprecations, since we test them
error_reporting(E_ALL ^ E_DEPRECATED);

// performance boost
gc_disable();

// load stubs
$stubLoader = new StubLoader();
$stubLoader->loadStubs();
