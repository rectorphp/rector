<?php

declare (strict_types=1);
namespace RectorPrefix202609;

use Rector\Config\RectorConfig;
use Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector;
use Rector\Php53\Rector\Ternary\TernaryToElvisRector;
use Rector\Php53\Rector\Variable\ReplaceHttpServerVarsByServerRector;
use Rector\Visibility\Rector\ClassMethod\ExplicitPublicClassMethodRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([ExplicitPublicClassMethodRector::class, TernaryToElvisRector::class, DirNameFileConstantToDirConstantRector::class, ReplaceHttpServerVarsByServerRector::class]);
};
