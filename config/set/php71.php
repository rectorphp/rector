<?php

declare(strict_types=1);

use Rector\Composer\Rector\ChangePackageVersionComposerRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Rector\Php71\Rector\Assign\AssignArrayToStringRector;
use Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector;
use Rector\Php71\Rector\BooleanOr\IsIterableRector;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Php71\Rector\List_\ListToArrayDestructRector;
use Rector\Php71\Rector\Name\ReservedObjectRector;
use Rector\Php71\Rector\TryCatch\MultiExceptionCatchRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(IsIterableRector::class);

    $services->set(ReservedObjectRector::class)
        ->call('configure', [[
            ReservedObjectRector::RESERVED_KEYWORDS_TO_REPLACEMENTS => [
                'Object' => 'BaseObject',
            ],
        ]]);

    $services->set(MultiExceptionCatchRector::class);

    $services->set(AssignArrayToStringRector::class);

    $services->set(CountOnNullRector::class);

    $services->set(RemoveExtraParametersRector::class);

    $services->set(BinaryOpBetweenNumberAndStringRector::class);

    $services->set(ListToArrayDestructRector::class);

    $services->set(ChangePackageVersionComposerRector::class)
        ->call('configure', [[
            ChangePackageVersionComposerRector::PACKAGES_AND_VERSIONS => ValueObjectInliner::inline([
                new PackageAndVersion('php', '^7.1'),
            ]),
        ]]);
};
