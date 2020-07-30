<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Tests\RectorGenerator\Source;

use PhpParser\Node\Expr\MethodCall;
use Rector\RectorGenerator\ValueObject\RecipeOption;

final class StaticRectorRecipeFactory
{
    public static function createWithConfiguration(): array
    {
        return [
            RecipeOption::PACKAGE => 'ModeratePackage',
            RecipeOption::NAME => 'WhateverRector',
            RecipeOption::NODE_TYPES => [
                MethodCall::class,
            ],
            RecipeOption::DESCRIPTION => 'Change $service->arg(...) to $service->call(...)',
            RecipeOption::CODE_BEFORE => <<<'CODE_SAMPLE'
<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeClass::class)
        ->arg('$key', 'value');
}
CODE_SAMPLE,
            RecipeOption::CODE_AFTER => <<<'CODE_SAMPLE'
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
            RecipeOption::SOURCE => null,
            // e.g. symfony30, target set to add this Rule to; keep null if part of core
            RecipeOption::SET => null,

            // OPTIONAL: only when configured
            RecipeOption::RULE_CONFIGURATION => [
                'CLASS_TYPE_TO_METHOD_NAME' => [
                    'SomeClass' => 'configure'
                ]
            ],

            // OPTIONAL: extra file
            RecipeOption::EXTRA_FILE_NAME => null,
            RecipeOption::EXTRA_FILE_CONTENT => null,
        ];
    }
}
