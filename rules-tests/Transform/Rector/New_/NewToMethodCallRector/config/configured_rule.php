<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Source\MyClass;
use Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Source\MyClassFactory;
use Rector\Transform\Rector\New_\NewToMethodCallRector;
use Rector\Transform\ValueObject\NewToMethodCall;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(
            NewToMethodCallRector::class,
            [new NewToMethodCall(MyClass::class, MyClassFactory::class, 'create')]
        );
};
