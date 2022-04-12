<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Source\MyClass;
use Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Source\MyClassFactory;
use Rector\Transform\Rector\New_\NewToMethodCallRector;
use Rector\Transform\ValueObject\NewToMethodCall;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(NewToMethodCallRector::class)
        ->configure([new NewToMethodCall(MyClass::class, MyClassFactory::class, 'create')]);
};
