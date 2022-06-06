<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\TypeInferer;

use RectorPrefix20220606\PHPStan\Type\Generic\GenericObjectType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\TypeWithClassName;
final class SplArrayFixedTypeNarrower
{
    public function narrow(Type $paramType) : Type
    {
        if ($paramType->isSuperTypeOf(new ObjectType('SplFixedArray'))->no()) {
            return $paramType;
        }
        if (!$paramType instanceof TypeWithClassName) {
            return $paramType;
        }
        if ($paramType instanceof GenericObjectType) {
            return $paramType;
        }
        $types = [];
        if ($paramType->getClassName() === 'PhpCsFixer\\Tokenizer\\Tokens') {
            $types[] = new ObjectType('PhpCsFixer\\Tokenizer\\Token');
        }
        if ($paramType->getClassName() === 'PhpCsFixer\\Doctrine\\Annotation\\Tokens') {
            $types[] = new ObjectType('PhpCsFixer\\Doctrine\\Annotation\\Token');
        }
        if ($types === []) {
            return $paramType;
        }
        return new GenericObjectType($paramType->getClassName(), $types);
    }
}
