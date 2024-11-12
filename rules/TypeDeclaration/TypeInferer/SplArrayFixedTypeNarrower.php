<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer;

use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
final class SplArrayFixedTypeNarrower
{
    public function narrow(Type $paramType) : Type
    {
        if ($paramType->isSuperTypeOf(new ObjectType('SplFixedArray'))->no()) {
            return $paramType;
        }
        $className = ClassNameFromObjectTypeResolver::resolve($paramType);
        if ($className === null) {
            return $paramType;
        }
        if ($paramType instanceof GenericObjectType) {
            return $paramType;
        }
        $types = [];
        if ($className === 'PhpCsFixer\\Tokenizer\\Tokens') {
            $types[] = new ObjectType('PhpCsFixer\\Tokenizer\\Token');
        }
        if ($className === 'PhpCsFixer\\Doctrine\\Annotation\\Tokens') {
            $types[] = new ObjectType('PhpCsFixer\\Doctrine\\Annotation\\Token');
        }
        if ($types === []) {
            return $paramType;
        }
        return new GenericObjectType($className, $types);
    }
}
