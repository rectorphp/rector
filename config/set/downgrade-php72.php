<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector;
use Rector\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeObjectTypeDeclarationRector::class);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_71);

    $services->set(DowngradeParameterTypeWideningRector::class);
};
