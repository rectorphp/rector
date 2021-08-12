<?php

declare (strict_types=1);
namespace Rector\Php81\NodeFactory;

use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class EnumFactory
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var \PhpParser\BuilderFactory
     */
    private $builderFactory;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \PhpParser\BuilderFactory $builderFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->builderFactory = $builderFactory;
    }
    public function createFromClass(\PhpParser\Node\Stmt\Class_ $class) : \PhpParser\Node\Stmt\Enum_
    {
        $shortClassName = $this->nodeNameResolver->getShortName($class);
        $enum = new \PhpParser\Node\Stmt\Enum_($shortClassName);
        // constant to cases
        foreach ($class->getConstants() as $classConst) {
            $enum->stmts[] = $this->createEnumCaseFromConst($classConst);
        }
        return $enum;
    }
    public function createFromSpatieClass(\PhpParser\Node\Stmt\Class_ $class) : \PhpParser\Node\Stmt\Enum_
    {
        $shortClassName = $this->nodeNameResolver->getShortName($class);
        $enum = new \PhpParser\Node\Stmt\Enum_($shortClassName);
        // constant to cases
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($class);
        $docBlockMethods = ($phpDocInfo2 = $phpDocInfo) ? $phpDocInfo2->getTagsByName('@method') : null;
        if ($docBlockMethods !== null) {
            foreach ($docBlockMethods as $docBlockMethod) {
                $enum->stmts[] = $this->createEnumCaseFromDocComment($docBlockMethod);
            }
        }
        return $enum;
    }
    private function createEnumCaseFromConst(\PhpParser\Node\Stmt\ClassConst $classConst) : \PhpParser\Node\Stmt\EnumCase
    {
        $constConst = $classConst->consts[0];
        $enumCase = new \PhpParser\Node\Stmt\EnumCase($constConst->name, $constConst->value);
        // mirror comments
        $enumCase->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, $classConst->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO));
        $enumCase->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS, $classConst->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS));
        return $enumCase;
    }
    private function createEnumCaseFromDocComment(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $phpDocTagNode) : \PhpParser\Node\Stmt\EnumCase
    {
        /**
         * @var MethodTagValueNode $nodeValue
         */
        $nodeValue = $phpDocTagNode->value;
        return new \PhpParser\Node\Stmt\EnumCase($nodeValue->methodName, $this->builderFactory->val($nodeValue->methodName));
    }
}
