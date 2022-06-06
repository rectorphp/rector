<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector;
use RectorPrefix20220606\Rector\Php53\Rector\Ternary\TernaryToElvisRector;
use RectorPrefix20220606\Rector\Php53\Rector\Variable\ReplaceHttpServerVarsByServerRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(TernaryToElvisRector::class);
    $rectorConfig->rule(DirNameFileConstantToDirConstantRector::class);
    $rectorConfig->rule(ReplaceHttpServerVarsByServerRector::class);
};
