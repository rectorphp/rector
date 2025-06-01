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
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\\Component\\PropertyAccess\\PropertyPathInterface', 'getLength', new IntegerType()), new AddReturnTypeDeclaration('Symfony\\Component\\PropertyAccess\\PropertyPathInterface', 'getParent', new UnionType([new NullType(), new ObjectType('Symfony\\Component\\PropertyAccess\\PropertyPathInterface')])), new AddReturnTypeDeclaration('Symfony\\Component\\PropertyAccess\\PropertyPathInterface', 'getElements', $arrayType), new AddReturnTypeDeclaration('Symfony\\Component\\PropertyAccess\\PropertyPathInterface', 'getElement', new StringType()), new AddReturnTypeDeclaration('Symfony\\Component\\PropertyAccess\\PropertyPathInterface', 'isProperty', new BooleanType()), new AddReturnTypeDeclaration('Symfony\\Component\\PropertyAccess\\PropertyPathInterface', 'isIndex', new BooleanType())]);
};
