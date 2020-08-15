<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Tests\RectorGenerator\Source;

use PhpParser\Node\Expr\MethodCall;
use Rector\RectorGenerator\ValueObject\RectorRecipe;

final class StaticRectorRecipeFactory
{
    public static function createRectorRecipe(bool $isRectorRepository): RectorRecipe
    {
        return new RectorRecipe(
            'ModeratePackage',
            'WhateverRector',
            [MethodCall::class],
            'Change $service->arg(...) to $service->call(...)',
            <<<'CODE_SAMPLE'
<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeClass::class)
        ->arg('$key', 'value');
}
CODE_SAMPLE,
            <<<'CODE_SAMPLE'
<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeClass::class)
        ->call('configure', [[
            '$key' => 'value'
        ]]);
}
CODE_SAMPLE,
            // e.g. link to RFC or headline in upgrade guide, 1 or more in the list
            [],
            // e.g. symfony30, target set to add this Rule to; keep null if part of core
            null,

            // OPTIONAL: only when configured
            [
                'CLASS_TYPE_TO_METHOD_NAME' => [
                    'SomeClass' => 'configure'
                ]
            ],
            null,
            null,
            $isRectorRepository
        );
    }
}
