<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\Config\RectorConfig;
use Rector\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector;
use Rector\Php70\Rector\FunctionLike\ExceptionHandlerTypehintRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ExceptionHandlerTypehintRector::class);
    $rectorConfig->rule(CatchExceptionNameMatchingTypeRector::class);
    $rectorConfig->rule(AddDefaultValueForUndefinedVariableRector::class);
};
