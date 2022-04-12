<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Transform\Rector\FuncCall\FuncCallToStaticCallRector;
use Rector\Transform\ValueObject\FuncCallToStaticCall;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(FuncCallToStaticCallRector::class)
        ->configure([
            new FuncCallToStaticCall('view', 'SomeStaticClass', 'render'),
            new FuncCallToStaticCall('SomeNamespaced\view', 'AnotherStaticClass', 'render'),
        ]);
};
