<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source\FirstDependency;
use Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source\SecondDependency;
use Rector\Transform\Rector\MethodCall\MethodCallToMethodCallRector;
use Rector\Transform\ValueObject\MethodCallToMethodCall;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();

    $services->set(MethodCallToMethodCallRector::class)
        ->configure([new MethodCallToMethodCall(FirstDependency::class, 'go', SecondDependency::class, 'away')]);
};
