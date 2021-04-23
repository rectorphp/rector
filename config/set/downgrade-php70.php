<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector;
use Rector\DowngradePhp70\Rector\Declare_\DowngradeStrictTypeDeclarationRector;
use Rector\DowngradePhp70\Rector\FunctionLike\DowngradeTypeDeclarationRector;
use Rector\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_56);

    $services = $containerConfigurator->services();
    $services->set(DowngradeTypeDeclarationRector::class);
    $services->set(DowngradeStrictTypeDeclarationRector::class);
    $services->set(DowngradeAnonymousClassRector::class);
    $services->set(DowngradeNullCoalesceRector::class);
};
