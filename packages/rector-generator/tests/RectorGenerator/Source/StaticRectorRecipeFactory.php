<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Tests\RectorGenerator\Source;

use PhpParser\Node\Expr\MethodCall;
use Rector\RectorGenerator\ValueObject\RectorRecipe;
use Rector\Set\ValueObject\SetList;

final class StaticRectorRecipeFactory
{
    public static function createRectorRecipe(bool $isRectorRepository): RectorRecipe
    {
        $rectorRecipe = new RectorRecipe(
            'Utils',
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
CODE_SAMPLE
            , <<<'CODE_SAMPLE'
<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeClass::class)
        ->call('configure', [[
            '$key' => 'value'
        ]]);
}
CODE_SAMPLE
        );

        $rectorRecipe->setConfiguration([
            'CLASS_TYPE_TO_METHOD_NAME' => [
                'SomeClass' => 'configure'
            ]
        ]);

        $rectorRecipe->setIsRectorRepository($isRectorRepository);

        if ($isRectorRepository) {
            $rectorRecipe->setPackage('ModeratePackage');
        }

        $rectorRecipe->setSet(SetList::DEAD_CODE);

        return $rectorRecipe;
    }
}
