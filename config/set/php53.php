<?php

declare (strict_types=1);
namespace RectorPrefix202410;

use Rector\Config\RectorConfig;
use Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector;
use Rector\Php53\Rector\Ternary\TernaryToElvisRector;
use Rector\Php53\Rector\Variable\ReplaceHttpServerVarsByServerRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([TernaryToElvisRector::class, DirNameFileConstantToDirConstantRector::class, ReplaceHttpServerVarsByServerRector::class]);
};
