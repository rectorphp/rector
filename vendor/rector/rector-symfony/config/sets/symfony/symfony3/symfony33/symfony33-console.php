<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Symfony33\Rector\ClassConstFetch\ConsoleExceptionToErrorEventConstantRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([ConsoleExceptionToErrorEventConstantRector::class]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // console
        'Symfony\\Component\\Console\\Event\\ConsoleExceptionEvent' => 'Symfony\\Component\\Console\\Event\\ConsoleErrorEvent',
    ]);
};
