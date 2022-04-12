<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector\Source\RenameToProperty;

use Rector\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector;
use Rector\Transform\ValueObject\MethodCallToPropertyFetch;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(MethodCallToPropertyFetchRector::class)
        ->configure([new MethodCallToPropertyFetch(RenameToProperty::class, 'getEntityManager', 'entityManager')]);
};
