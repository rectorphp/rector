<?php

declare(strict_types=1);

use Rector\Composer\Rector\ChangePackageVersionComposerRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::SETS, [
        SetList::NETTE_30,
    ]);

    $services = $containerConfigurator->services();
    $services->set(ChangePackageVersionComposerRector::class)
        ->call('configure', [[
            ChangePackageVersionComposerRector::PACKAGES_AND_VERSIONS => ValueObjectInliner::inline([
                new PackageAndVersion('nette/application', '~3.0.6'),
                new PackageAndVersion('latte/latte', '~2.8.3'),
            ]),
        ]]);
};
