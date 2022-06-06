<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\NodeManipulator;

use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\NullableType;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PHPStan\Type\NullType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
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
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, PhpDocTypeChanger $phpDocTypeChanger, NodeTypeResolver $nodeTypeResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\NullableType $replaceIntoType
     */
    public function refactorFunctionReturnType(ClassMethod $classMethod, ObjectType $objectType, $replaceIntoType, Type $phpDocType) : ?ClassMethod
    {
        $returnType = $classMethod->returnType;
        if ($returnType === null) {
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
            if ($phpDocType instanceof UnionType) {
                $item0Unpacked = $phpDocType->getTypes();
                // Adding a UnionType into a new UnionType throws an exception so we need to "unpack" the types
                $phpDocType = new UnionType(\array_merge($item0Unpacked, [new NullType()]));
            } else {
                $phpDocType = new UnionType([$phpDocType, new NullType()]);
            }
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
