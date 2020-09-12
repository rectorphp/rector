<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Generic\ValueObject\AddReturnTypeDeclaration;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[
            AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => inline_value_objects([
                new AddReturnTypeDeclaration('PhpSpec\ObjectBehavior', 'getMatchers', 'array'),
            ]),
        ]]);
};
