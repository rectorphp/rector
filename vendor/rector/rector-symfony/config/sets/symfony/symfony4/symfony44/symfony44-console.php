<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony44\Rector\ClassMethod\ConsoleExecuteReturnIntRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // https://github.com/symfony/symfony/pull/33775
        ConsoleExecuteReturnIntRector::class,
    ]);
};
