<?php

declare(strict_types=1);

use Rector\Arguments\Rector\FuncCall\SwapFuncCallArgumentsRector;
use Rector\Arguments\ValueObject\SwapFuncCallArguments;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(SwapFuncCallArgumentsRector::class)
        ->configure([new SwapFuncCallArguments('some_function', [1, 0])]);
};
