<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig) : void {
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $nullableStringType = new UnionType([new NullType(), new StringType()]);
    $typeGuessType = new ObjectType('Symfony\\Component\\Form\\Guess\\TypeGuess');
    $nullableValueGuessType = new UnionType([new NullType(), new ObjectType('Symfony\\Component\\Form\\Guess\\ValueGuess')]);
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\\Component\\Form\\AbstractExtension', 'loadTypes', $arrayType), new AddReturnTypeDeclaration('Symfony\\Component\\Form\\AbstractExtension', 'loadTypeGuesser', new UnionType([new NullType(), new ObjectType('Symfony\\Component\\Form\\FormTypeGuesserInterface')])), new AddReturnTypeDeclaration('Symfony\\Component\\Form\\AbstractRendererEngine', 'loadResourceForBlockName', new BooleanType()), new AddReturnTypeDeclaration('Symfony\\Component\\Form\\AbstractType', 'getBlockPrefix', new StringType()), new AddReturnTypeDeclaration('Symfony\\Component\\Form\\AbstractType', 'getParent', $nullableStringType), new AddReturnTypeDeclaration('Symfony\\Component\\Form\\DataTransformerInterface', 'transform', new MixedType()), new AddReturnTypeDeclaration('Symfony\\Component\\Form\\DataTransformerInterface', 'reverseTransform', new MixedType()), new AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormRendererEngineInterface', 'renderBlock', new StringType()), new AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeGuesserInterface', 'guessType', new UnionType([new NullType(), $typeGuessType])), new AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeGuesserInterface', 'guessRequired', $nullableValueGuessType), new AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeGuesserInterface', 'guessMaxLength', $nullableValueGuessType), new AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeGuesserInterface', 'guessPattern', $nullableValueGuessType), new AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeInterface', 'getBlockPrefix', new StringType()), new AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeInterface', 'getParent', $nullableStringType), new AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeInterface', 'buildForm', new VoidType()), new AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeInterface', 'configureOptions', new VoidType())]);
};
