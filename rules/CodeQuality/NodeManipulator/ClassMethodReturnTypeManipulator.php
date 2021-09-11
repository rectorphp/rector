<?php

declare(strict_types=1);

namespace Rector\CodeQuality\NodeManipulator;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class ClassMethodReturnTypeManipulator
{
    public function __construct(
        private PhpDocInfoFactory $phpDocInfoFactory,
        private PhpDocTypeChanger $phpDocTypeChanger,
        private NodeTypeResolver $nodeTypeResolver
    ) {
    }

    public function refactorFunctionReturnType(
        ClassMethod $classMethod,
        ObjectType $objectType,
        Identifier|Name|NullableType $replaceIntoType,
        Type $phpDocType
    ): ?ClassMethod {
        $returnType = $classMethod->returnType;
        if ($returnType === null) {
            return null;
        }

        $isNullable = false;
        if ($returnType instanceof NullableType) {
            $isNullable = true;
            $returnType = $returnType->type;
        }

        if (! $this->nodeTypeResolver->isObjectType($returnType, $objectType)) {
            return null;
        }

        $paramType = $this->nodeTypeResolver->resolve($returnType);
        if (! $paramType->isSuperTypeOf($objectType)->yes()) {
            return null;
        }

        if ($isNullable) {
            if ($phpDocType instanceof UnionType) {
                // Adding a UnionType into a new UnionType throws an exception so we need to "unpack" the types
                $phpDocType = new UnionType([...$phpDocType->getTypes(), new NullType()]);
            } else {
                $phpDocType = new UnionType([$phpDocType, new NullType()]);
            }

            if (! $replaceIntoType instanceof NullableType) {
                $replaceIntoType = new NullableType($replaceIntoType);
            }
        }

        $classMethod->returnType = $replaceIntoType;

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $phpDocType);

        return $classMethod;
    }
}
