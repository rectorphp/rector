<?php

declare(strict_types=1);

use Rector\Composer\Rector\RenamePackageComposerRector;
use Rector\Composer\ValueObject\RenamePackage;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenamePackageComposerRector::class)
        ->call('configure', [
            [
                RenamePackageComposerRector::RENAME_PACKAGES =>
                    ValueObjectInliner::inline(
                        [new RenamePackage('foo/bar', 'baz/bar'), new RenamePackage('foo/baz', 'baz/baz')]
                    ),
            ],
        ]);
};
