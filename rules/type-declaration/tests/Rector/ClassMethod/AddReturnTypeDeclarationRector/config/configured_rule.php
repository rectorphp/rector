<?php

declare(strict_types=1);

use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\PHPUnitTestCase;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $nullableObjectUnionType = new UnionType([new ObjectType('SomeType'), new NullType()]);

    $services = $containerConfigurator->services();

    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[
            AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => ValueObjectInliner::inline([
                new AddReturnTypeDeclaration(
                    'Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture\Fixture',
                    'parse',
                    $arrayType
                ),
                new AddReturnTypeDeclaration(
                    'Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture\Fixture',
                    'resolve',
                    new ObjectType('SomeType')
                ),
                new AddReturnTypeDeclaration(
                    'Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture\Fixture',
                    'nullable',
                    $nullableObjectUnionType
                ),
                new AddReturnTypeDeclaration(
                    'Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture\RemoveReturnType',
                    'clear',
                    new MixedType()
                ),
                new AddReturnTypeDeclaration(PHPUnitTestCase::class, 'tearDown', new VoidType()),
            ]),
        ]]);
};
