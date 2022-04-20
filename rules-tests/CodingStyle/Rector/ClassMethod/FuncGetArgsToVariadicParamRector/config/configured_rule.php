<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(FuncGetArgsToVariadicParamRector::class);
};
