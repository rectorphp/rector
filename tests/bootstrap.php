<?php

declare(strict_types=1);

use Rector\Core\Stubs\PHPStanStubLoader;
use Tracy\Debugger;

require_once __DIR__ . '/../vendor/autoload.php';

// silent deprecations, since we test them
error_reporting(E_ALL ^ E_DEPRECATED);

// performance boost
gc_disable();

$phpStanStubLoader = new PHPStanStubLoader();
$phpStanStubLoader->loadStubs();

// make dump() useful and not nest infinity spam
Debugger::$maxDepth = 2;
