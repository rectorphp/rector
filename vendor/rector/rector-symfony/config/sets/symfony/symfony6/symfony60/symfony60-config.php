<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Symfony\\Component\\Config\\Loader\\LoaderInterface', 'load', 0, new MixedType(\true)), new AddParamTypeDeclaration('Symfony\\Component\\Config\\Loader\\LoaderInterface', 'supports', 0, new MixedType(\true))]);
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\LoaderInterface', 'load', new MixedType()), new AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\Loader', 'import', new MixedType()), new AddReturnTypeDeclaration('Symfony\\Component\\Config\\Definition\\ConfigurationInterface', 'getConfigTreeBuilder', new ObjectType('Symfony\\Component\\Config\\Definition\\Builder\\TreeBuilder')), new AddReturnTypeDeclaration('Symfony\\Component\\Config\\FileLocator', 'locate', new UnionType([new StringType(), $arrayType])), new AddReturnTypeDeclaration('Symfony\\Component\\Config\\FileLocatorInterface', 'locate', new UnionType([new StringType(), $arrayType])), new AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\FileLoader', 'import', new MixedType()), new AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\Loader', 'import', new MixedType()), new AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\LoaderInterface', 'load', new MixedType()), new AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\LoaderInterface', 'supports', new BooleanType()), new AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\LoaderInterface', 'getResolver', new ObjectType('Symfony\\Component\\Config\\Loader\\LoaderResolverInterface')), new AddReturnTypeDeclaration('Symfony\\Component\\Config\\ResourceCheckerInterface', 'supports', new BooleanType()), new AddReturnTypeDeclaration('Symfony\\Component\\Config\\ResourceCheckerInterface', 'isFresh', new BooleanType())]);
};
