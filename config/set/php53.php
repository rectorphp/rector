<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector;
use Rector\Php53\Rector\Ternary\TernaryToElvisRector;
use Rector\Php53\Rector\Variable\ReplaceHttpServerVarsByServerRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(TernaryToElvisRector::class);
    $rectorConfig->rule(DirNameFileConstantToDirConstantRector::class);
    $rectorConfig->rule(ReplaceHttpServerVarsByServerRector::class);
};
