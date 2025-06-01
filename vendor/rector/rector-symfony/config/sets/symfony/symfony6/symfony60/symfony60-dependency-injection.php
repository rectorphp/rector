<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony60\Rector\FuncCall\ReplaceServiceArgumentRector;
use Rector\Symfony\ValueObject\ReplaceServiceArgument;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(ReplaceServiceArgumentRector::class, [new ReplaceServiceArgument('Psr\\Container\\ContainerInterface', new String_('service_container')), new ReplaceServiceArgument('Symfony\\Component\\DependencyInjection\\ContainerInterface', new String_('service_container'))]);
    $configurationType = new ObjectType('Symfony\\Component\\Config\\Definition\\ConfigurationInterface');
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $scalarTypes = [$arrayType, new BooleanType(), new StringType(), new IntegerType(), new FloatType(), new NullType()];
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Compiler\\AbstractRecursivePass', 'processValue', new MixedType()), new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\ConfigurationExtensionInterface', 'getConfiguration', new UnionType([new NullType(), $configurationType])), new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\Extension', 'getXsdValidationBasePath', new UnionType([new StringType(), new ConstantBooleanType(\false)])), new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\Extension', 'getNamespace', new StringType()), new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\Extension', 'getConfiguration', new UnionType([new NullType(), $configurationType])), new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface', 'getNamespace', new StringType()), new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface', 'getXsdValidationBasePath', new UnionType([new StringType(), new ConstantBooleanType(\false)])), new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface', 'getAlias', new StringType()), new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\LazyProxy\\Instantiator\\InstantiatorInterface', 'instantiateProxy', new ObjectWithoutClassType()), new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Container', 'getParameter', new UnionType($scalarTypes)), new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\ContainerInterface', 'getParameter', new UnionType($scalarTypes))]);
};
