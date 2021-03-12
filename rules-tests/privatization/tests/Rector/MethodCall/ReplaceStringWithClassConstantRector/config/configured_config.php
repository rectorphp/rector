<?php

declare(strict_types=1);

use Rector\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector;
use Rector\Privatization\Tests\Rector\MethodCall\ReplaceStringWithClassConstantRector\Source\Placeholder;
use Rector\Privatization\ValueObject\ReplaceStringWithClassConstant;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReplaceStringWithClassConstantRector::class)
        ->call('configure', [[
            ReplaceStringWithClassConstantRector::REPLACE_STRING_WITH_CLASS_CONSTANT => ValueObjectInliner::inline([
                new ReplaceStringWithClassConstant(
                    'Rector\Privatization\Tests\Rector\MethodCall\ReplaceStringWithClassConstantRector\Fixture\ReplaceWithConstant',
                    'call',
                    0,
                    Placeholder::class
                ),
            ]),
        ]]);
};
