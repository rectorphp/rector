<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\MethodCall\CallableInMethodCallToVariableRector\Source\DummyCache;
use Rector\Transform\Rector\MethodCall\CallableInMethodCallToVariableRector;
use Rector\Transform\ValueObject\CallableInMethodCallToVariable;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(CallableInMethodCallToVariableRector::class)
        ->configure([new CallableInMethodCallToVariable(DummyCache::class, 'save', 1)]);
};
