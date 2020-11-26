<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\ValueObject\ArgumentAdder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symplify\SymfonyPhpConfig\inline_value_objects;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentAdderRector::class)
        ->call('configure', [[
            ArgumentAdderRector::ADDED_ARGUMENTS => inline_value_objects([
                new ArgumentAdder(
                    'Symfony\Component\DependencyInjection\ContainerBuilder',
                    'addCompilerPass',
                    2,
                    'priority',
                    '0'
                ),
            ]),
        ]]);
};
