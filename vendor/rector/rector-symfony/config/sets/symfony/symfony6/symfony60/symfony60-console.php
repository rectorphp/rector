<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig) : void {
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $commandType = new ObjectType('Symfony\\Component\\Console\\Command\\Command');
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [
        // @see https://github.com/symfony/symfony/pull/43028/files
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Helper\\HelperInterface', 'getName', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'doRun', new IntegerType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'getLongVersion', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'add', new UnionType([new NullType(), $commandType])),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'get', $commandType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'find', $commandType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'all', $arrayType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'doRunCommand', new IntegerType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'isEnabled', new BooleanType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'execute', new IntegerType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Helper\\HelperInterface', 'getName', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Input\\InputInterface', 'getParameterOption', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Input\\InputInterface', 'getArgument', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Input\\InputInterface', 'getOption', new MixedType()),
    ]);
};
