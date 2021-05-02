<?php

declare(strict_types=1);

use Rector\Core\Stubs\PHPStanStubLoader;

<<<<<<< HEAD
require_once __DIR__ . '/../src/constants.php';

// make local php-parser a priority to avoid conflict
require_once __DIR__ . '/../preload.php';
=======
// to give local php-parser priority over PHPStan
require __DIR__ . '/../preload.php';
>>>>>>> df2aaaa3c (add preload to test case)
require_once __DIR__ . '/../vendor/autoload.php';


// silent deprecations, since we test them
error_reporting(E_ALL ^ E_DEPRECATED);

// performance boost
gc_disable();

$phpStanStubLoader = new PHPStanStubLoader();
$phpStanStubLoader->loadStubs();
