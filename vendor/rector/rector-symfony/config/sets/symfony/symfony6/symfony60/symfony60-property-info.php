<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\UnionType;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig) : void {
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $nullableBooleanType = new UnionType([new NullType(), new BooleanType()]);
    $nullableArrayType = new UnionType([new NullType(), $arrayType]);
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\\Component\\PropertyInfo\\PropertyAccessExtractorInterface', 'isReadable', $nullableBooleanType), new AddReturnTypeDeclaration('Symfony\\Component\\PropertyInfo\\PropertyAccessExtractorInterface', 'isWritable', $nullableBooleanType), new AddReturnTypeDeclaration('Symfony\\Component\\PropertyInfo\\PropertyListExtractorInterface', 'getProperties', $nullableArrayType), new AddReturnTypeDeclaration('Symfony\\Component\\PropertyInfo\\PropertyTypeExtractorInterface', 'getTypes', $nullableArrayType)]);
};
