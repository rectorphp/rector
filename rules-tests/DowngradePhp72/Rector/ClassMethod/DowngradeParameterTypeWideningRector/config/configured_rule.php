<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Rector\Config\RectorConfig;
use Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector;
use Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture\SomeContainerInterface;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(DowngradeParameterTypeWideningRector::class, [
            ContainerInterface::class => ['set', 'get', 'has', 'initialized'],
            SomeContainerInterface::class => ['set', 'has'],
        ]);
};
