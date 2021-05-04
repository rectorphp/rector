<?php

declare(strict_types=1);

use Rector\Core\Stubs\PHPStanStubLoader;

require_once __DIR__ . '/../src/constants.php';

// make local php-parser a priority to avoid conflict
require_once __DIR__ . '/../preload.php';
require_once __DIR__ . '/../vendor/autoload.php';

// silent deprecations, since we test them
error_reporting(E_ALL ^ E_DEPRECATED);

// performance boost
gc_disable();

$phpStanStubLoader = new PHPStanStubLoader();
$phpStanStubLoader->loadStubs();
