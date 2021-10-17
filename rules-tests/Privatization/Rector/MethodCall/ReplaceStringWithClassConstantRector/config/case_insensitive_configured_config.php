<?php

declare(strict_types=1);

use Rector\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector;
use Rector\Privatization\ValueObject\ReplaceStringWithClassConstant;
use Rector\Tests\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector\Source\Order;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ReplaceStringWithClassConstantRector::class)
        ->call('configure', [[
            ReplaceStringWithClassConstantRector::REPLACE_STRING_WITH_CLASS_CONSTANT => ValueObjectInliner::inline([
                new ReplaceStringWithClassConstant(
                    'Rector\Tests\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector\FixtureCaseInsensitive\ReplaceWithConstant',
                    'call',
                    0,
                    Order::class,
                    true
                ),
            ]),
        ]]);
};
