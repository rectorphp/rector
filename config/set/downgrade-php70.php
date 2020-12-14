<?php

declare(strict_types=1);

use Rector\Downgrade\Rector\LNumber\ChangePhpVersionInPlatformCheckRector;
use Rector\DowngradePhp70\Rector\FunctionLike\DowngradeTypeParamDeclarationRector;
use Rector\DowngradePhp70\Rector\FunctionLike\DowngradeTypeReturnDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeTypeParamDeclarationRector::class);
    $services->set(DowngradeTypeReturnDeclarationRector::class);
    $services->set(ChangePhpVersionInPlatformCheckRector::class)
        ->call('configure', [[
            ChangePhpVersionInPlatformCheckRector::TARGET_PHP_VERSION => 50600,
        ]]);
};
