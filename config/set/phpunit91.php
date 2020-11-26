<?php

declare(strict_types=1);

use Rector\PHPUnit\Rector\Class_\AddProphecyTraitRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symplify\SymfonyPhpConfig\inline_value_objects;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddProphecyTraitRector::class);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects([
                new MethodCallRename('PHPUnit\Framework\TestCase', 'assertFileNotExists', 'assertFileDoesNotExist'),
            ]),
        ]]);
};
