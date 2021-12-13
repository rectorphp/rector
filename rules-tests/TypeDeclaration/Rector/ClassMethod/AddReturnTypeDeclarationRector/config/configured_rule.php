<?php

declare(strict_types=1);

use PHPStan\Type\MixedType;
use PHPStan\Type\VoidType;
use Rector\StaticTypeMapper\ValueObject\Type\SimpleStaticType;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture\ReturnOfStatic;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture\ReturnTheMixed;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\PHPUnitTestCase;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddReturnTypeDeclarationRector::class)
        ->configure([
            new AddReturnTypeDeclaration(PHPUnitTestCase::class, 'tearDown', new VoidType()),
            new AddReturnTypeDeclaration(ReturnTheMixed::class, 'create', new MixedType(true)),
            new AddReturnTypeDeclaration(
                ReturnOfStatic::class,
                'create',
                new SimpleStaticType(ReturnOfStatic::class)
            ),
        ]);
};
