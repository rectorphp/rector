<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer;

use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
final class SplArrayFixedTypeNarrower
{
    public function narrow(\PHPStan\Type\Type $paramType) : \PHPStan\Type\Type
    {
        if ($paramType->isSuperTypeOf(new \PHPStan\Type\ObjectType('SplFixedArray'))->no()) {
            return $paramType;
        }
        if (!$paramType instanceof \PHPStan\Type\TypeWithClassName) {
            return $paramType;
        }
        if ($paramType instanceof \PHPStan\Type\Generic\GenericObjectType) {
            return $paramType;
        }
        $types = [];
        if ($paramType->getClassName() === 'PhpCsFixer\\Tokenizer\\Tokens') {
            $types[] = new \PHPStan\Type\ObjectType('PhpCsFixer\\Tokenizer\\Token');
        }
        if ($paramType->getClassName() === 'PhpCsFixer\\Doctrine\\Annotation\\Tokens') {
            $types[] = new \PHPStan\Type\ObjectType('PhpCsFixer\\Doctrine\\Annotation\\Token');
        }
        if ($types === []) {
            return $paramType;
        }
        return new \PHPStan\Type\Generic\GenericObjectType($paramType->getClassName(), $types);
    }
}
