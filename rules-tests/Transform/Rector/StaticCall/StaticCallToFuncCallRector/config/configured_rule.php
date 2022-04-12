<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\StaticCall\StaticCallToFuncCallRector\Source\SomeOldStaticClass;
use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\Transform\ValueObject\StaticCallToFuncCall;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(StaticCallToFuncCallRector::class)
        ->configure([new StaticCallToFuncCall(SomeOldStaticClass::class, 'render', 'view')]);
};
