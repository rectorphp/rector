<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddReturnTypeDeclarationRector::class)
        ->arg('$typehintForMethodByClass', [
            'PhpSpec\ObjectBehavior' => [
                # @see http://www.phpspec.net/en/stable/manual/upgrading-to-phpspec-4.html
                'getMatchers' => 'array',
            ],
        ]);
};
