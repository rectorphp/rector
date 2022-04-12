<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\Isset_\UnsetAndIssetToMethodCallRector\Source\LocalContainer;
use Rector\Transform\Rector\Isset_\UnsetAndIssetToMethodCallRector;
use Rector\Transform\ValueObject\UnsetAndIssetToMethodCall;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(UnsetAndIssetToMethodCallRector::class)
        ->configure([new UnsetAndIssetToMethodCall(LocalContainer::class, 'hasService', 'removeService')]);
};
