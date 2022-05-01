<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector;
use Rector\Php53\Rector\Ternary\TernaryToElvisRector;
use Rector\Php53\Rector\Variable\ReplaceHttpServerVarsByServerRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Php53\Rector\Ternary\TernaryToElvisRector::class);
    $rectorConfig->rule(\Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector::class);
    $rectorConfig->rule(\Rector\Php53\Rector\Variable\ReplaceHttpServerVarsByServerRector::class);
};
