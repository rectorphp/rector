#!/usr/bin/env php
<?php declare(strict_types=1);

use Nette\Utils\Strings;

require_once __DIR__ . '/bootstrap.php';

$binPath = __DIR__ . '/../build/bin/rector';

$binContent = file_get_contents($binPath);

// add constant to make clear the define('RECTOR_PREFIXED', true)
$binContent = Strings::replace(
    $binContent,
    '#namespace RectorPrefixed;#',
    'namespace RectorPrefixed;' . PHP_EOL . "define('RECTOR_PREFIXED', true);"
);

// save
file_put_contents($binPath, $binContent);
