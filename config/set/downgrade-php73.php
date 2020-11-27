<?php

declare(strict_types=1);

use Rector\Downgrade\Rector\LNumber\ChangePhpVersionInPlatformCheckRector;
use Rector\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector;
use Rector\DowngradePhp73\Rector\String_\DowngradeFlexibleHeredocSyntaxRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeFlexibleHeredocSyntaxRector::class);
    $services->set(DowngradeListReferenceAssignmentRector::class);
    $services->set(ChangePhpVersionInPlatformCheckRector::class)
        ->call('configure', [[
            ChangePhpVersionInPlatformCheckRector::TARGET_PHP_VERSION => 70300,
        ]]);
};
