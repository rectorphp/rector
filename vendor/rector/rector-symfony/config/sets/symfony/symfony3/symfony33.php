<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony33/symfony33-console.php');
    $rectorConfig->import(__DIR__ . '/symfony33/symfony33-debug.php');
    $rectorConfig->import(__DIR__ . '/symfony33/symfony33-dependency-injection.php');
    $rectorConfig->import(__DIR__ . '/symfony33/symfony33-framework-bundle.php');
};
