<?php

declare (strict_types=1);
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
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\UnionTypeFactory;
final class ClassMethodReturnTypeManipulator
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\UnionTypeFactory
     */
    private $unionTypeFactory;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, PhpDocTypeChanger $phpDocTypeChanger, NodeTypeResolver $nodeTypeResolver, UnionTypeFactory $unionTypeFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->unionTypeFactory = $unionTypeFactory;
    }
    /**
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\NullableType $replaceIntoType
     */
    public function refactorFunctionReturnType(ClassMethod $classMethod, ObjectType $objectType, $replaceIntoType, Type $phpDocType) : ?ClassMethod
    {
        $returnType = $classMethod->returnType;
        if (!$returnType instanceof Node) {
            return null;
        }
        $isNullable = \false;
        if ($returnType instanceof NullableType) {
            $isNullable = \true;
            $returnType = $returnType->type;
        }
        if (!$this->nodeTypeResolver->isObjectType($returnType, $objectType)) {
            return null;
        }
        $paramType = $this->nodeTypeResolver->getType($returnType);
        if (!$paramType->isSuperTypeOf($objectType)->yes()) {
            return null;
        }
        if ($isNullable) {
            $phpDocType = $this->unionTypeFactory->createNullableUnionType($phpDocType);
            if (!$replaceIntoType instanceof NullableType) {
                $replaceIntoType = new NullableType($replaceIntoType);
            }
        }
        $classMethod->returnType = $replaceIntoType;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $phpDocType);
        return $classMethod;
    }
}
