<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\MethodCall\MethodCallToStaticCallRector\Source\AnotherDependency;
use Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector;
use Rector\Transform\ValueObject\MethodCallToStaticCall;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(MethodCallToStaticCallRector::class, [
            new MethodCallToStaticCall(AnotherDependency::class, 'process', 'StaticCaller', 'anotherMethod'),

        ]);
};
