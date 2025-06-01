<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig) : void {
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $nullableStringType = new UnionType([new NullType(), new StringType()]);
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\\Component\\Validator\\Constraint', 'getDefaultOption', $nullableStringType), new AddReturnTypeDeclaration('Symfony\\Component\\Validator\\Constraint', 'getRequiredOptions', $arrayType), new AddReturnTypeDeclaration('Symfony\\Component\\Validator\\Constraint', 'validatedBy', new StringType()), new AddReturnTypeDeclaration('Symfony\\Component\\Validator\\Constraint', 'getTargets', new UnionType([new StringType(), $arrayType]))]);
};
