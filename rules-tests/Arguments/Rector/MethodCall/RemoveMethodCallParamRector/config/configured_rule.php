<?php

declare(strict_types=1);

use Rector\Arguments\Rector\MethodCall\RemoveMethodCallParamRector;
use Rector\Arguments\ValueObject\RemoveMethodCallParam;
use Rector\Config\RectorConfig;
use Rector\Tests\Arguments\Rector\MethodCall\RemoveMethodCallParamRector\Source\MethodCaller;
use Rector\Tests\Arguments\Rector\MethodCall\RemoveMethodCallParamRector\Source\StaticCaller;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(RemoveMethodCallParamRector::class, [
            new RemoveMethodCallParam(MethodCaller::class, 'process', 1),
            new RemoveMethodCallParam(StaticCaller::class, 'remove', 3),
        ]);
};
