<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Rector\Util\Reflection\PrivatesAccessor;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig) : void {
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $scalarTypes = [$arrayType, new BooleanType(), new StringType(), new IntegerType(), new FloatType(), new NullType()];
    $scalarArrayObjectUnionedTypes = \array_merge($scalarTypes, [new ObjectType('ArrayObject')]);
    // cannot be crated with \PHPStan\Type\UnionTypeHelper::sortTypes() as ObjectType requires a class reflection we do not have here
    $unionTypeReflectionClass = new \ReflectionClass(UnionType::class);
    /** @var UnionType $scalarArrayObjectUnionType */
    $scalarArrayObjectUnionType = $unionTypeReflectionClass->newInstanceWithoutConstructor();
    $privatesAccessor = new PrivatesAccessor();
    $privatesAccessor->setPrivateProperty($scalarArrayObjectUnionType, 'types', $scalarArrayObjectUnionedTypes);
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Encoder\\DecoderInterface', 'decode', new MixedType()), new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Encoder\\DecoderInterface', 'supportsDecoding', new BooleanType()), new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractNormalizer', 'getAllowedAttributes', new UnionType([$arrayType, new BooleanType()])), new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractNormalizer', 'isAllowedAttribute', new BooleanType()), new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractNormalizer', 'instantiateObject', new ObjectWithoutClassType()), new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'supportsNormalization', new BooleanType()), new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'instantiateObject', new ObjectWithoutClassType()), new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'extractAttributes', $arrayType), new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'getAttributeValue', new MixedType()), new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'supportsDenormalization', new BooleanType()), new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'denormalize', new MixedType()), new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\DenormalizerInterface', 'denormalize', new MixedType()), new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\DenormalizerInterface', 'supportsDenormalization', new BooleanType()), new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface', 'supportsNormalization', new BooleanType()), new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'normalize', $scalarArrayObjectUnionType), new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface', 'normalize', $scalarArrayObjectUnionType)]);
};
