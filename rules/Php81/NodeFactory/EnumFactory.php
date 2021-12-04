<?php

declare (strict_types=1);
namespace Rector\Php81\NodeFactory;

use PhpParser\BuilderFactory;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class EnumFactory
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \PhpParser\BuilderFactory
     */
    private $builderFactory;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \PhpParser\BuilderFactory $builderFactory, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->builderFactory = $builderFactory;
        $this->valueResolver = $valueResolver;
    }
    public function createFromClass(\PhpParser\Node\Stmt\Class_ $class) : \PhpParser\Node\Stmt\Enum_
    {
        $shortClassName = $this->nodeNameResolver->getShortName($class);
        $enum = new \PhpParser\Node\Stmt\Enum_($shortClassName);
        $constants = $class->getConstants();
        if ($constants !== []) {
            $value = $this->valueResolver->getValue($constants[0]->consts[0]->value);
            $enum->scalarType = \is_string($value) ? new \PhpParser\Node\Identifier('string') : new \PhpParser\Node\Identifier('int');
            // constant to cases
            foreach ($constants as $constant) {
                $enum->stmts[] = $this->createEnumCaseFromConst($constant);
            }
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
            $enum->scalarType = new \PhpParser\Node\Identifier('string');
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
        /** @var MethodTagValueNode $nodeValue */
        $nodeValue = $phpDocTagNode->value;
        return new \PhpParser\Node\Stmt\EnumCase($nodeValue->methodName, $this->builderFactory->val($nodeValue->methodName));
    }
}
