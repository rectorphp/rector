<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Downgrade\Rector\LNumber\ChangePhpVersionInPlatformCheckRector;
use Rector\DowngradePhp72\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector;
use Rector\DowngradePhp72\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeParamObjectTypeDeclarationRector::class);
    $services->set(DowngradeReturnObjectTypeDeclarationRector::class);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, '7.1');

    // skip root namespace classes, like \DateTime or \Exception [default: true]
    $parameters->set(Option::IMPORT_SHORT_CLASSES, false);

    // skip classes used in PHP DocBlocks, like in /** @var \Some\Class */ [default: true]
    $parameters->set(Option::IMPORT_DOC_BLOCKS, false);
    $services->set(ChangePhpVersionInPlatformCheckRector::class)
        ->call('configure', [[
            ChangePhpVersionInPlatformCheckRector::TARGET_PHP_VERSION => 70200,
        ]]);
};
