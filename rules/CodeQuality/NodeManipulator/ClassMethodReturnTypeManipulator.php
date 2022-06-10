<?php

declare(strict_types=1);

namespace Rector\CodeQuality\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\DowngradePhp72\UnionTypeFactory;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class ClassMethodReturnTypeManipulator
{
    public function __construct(
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly PhpDocTypeChanger $phpDocTypeChanger,
        private readonly NodeTypeResolver $nodeTypeResolver,
        private readonly UnionTypeFactory $unionTypeFactory,
    ) {
    }

    public function refactorFunctionReturnType(
        ClassMethod $classMethod,
        ObjectType $objectType,
        Identifier|Name|NullableType $replaceIntoType,
        Type $phpDocType
    ): ?ClassMethod {
        $returnType = $classMethod->returnType;
        if (! $returnType instanceof Node) {
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

        $paramType = $this->nodeTypeResolver->getType($returnType);
        if (! $paramType->isSuperTypeOf($objectType)->yes()) {
            return null;
        }

        if ($isNullable) {
            $phpDocType = $this->unionTypeFactory->createNullableUnionType($phpDocType);

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
