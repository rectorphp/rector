<?php

declare (strict_types=1);
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
    public function __construct(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\NullableType $replaceIntoType
     */
    public function refactorFunctionReturnType(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PHPStan\Type\ObjectType $objectType, $replaceIntoType, \PHPStan\Type\Type $phpDocType) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $returnType = $classMethod->returnType;
        if ($returnType === null) {
            return null;
        }
        $isNullable = \false;
        if ($returnType instanceof \PhpParser\Node\NullableType) {
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
            if ($phpDocType instanceof \PHPStan\Type\UnionType) {
                $item0Unpacked = $phpDocType->getTypes();
                // Adding a UnionType into a new UnionType throws an exception so we need to "unpack" the types
                $phpDocType = new \PHPStan\Type\UnionType(\array_merge($item0Unpacked, [new \PHPStan\Type\NullType()]));
            } else {
                $phpDocType = new \PHPStan\Type\UnionType([$phpDocType, new \PHPStan\Type\NullType()]);
            }
            if (!$replaceIntoType instanceof \PhpParser\Node\NullableType) {
                $replaceIntoType = new \PhpParser\Node\NullableType($replaceIntoType);
            }
        }
        $classMethod->returnType = $replaceIntoType;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $phpDocType);
        return $classMethod;
    }
}
