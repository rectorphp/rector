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
    public function __construct(NodeNameResolver $nodeNameResolver, PhpDocInfoFactory $phpDocInfoFactory, BuilderFactory $builderFactory, ValueResolver $valueResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->builderFactory = $builderFactory;
        $this->valueResolver = $valueResolver;
    }
    public function createFromClass(Class_ $class) : Enum_
    {
        $shortClassName = $this->nodeNameResolver->getShortName($class);
        $enum = new Enum_($shortClassName, [], ['startLine' => $class->getStartLine(), 'endLine' => $class->getEndLine()]);
        $enum->namespacedName = $class->namespacedName;
        $constants = $class->getConstants();
        $enum->stmts = $class->getTraitUses();
        if ($constants !== []) {
            $value = $this->valueResolver->getValue($constants[0]->consts[0]->value);
            $enum->scalarType = \is_string($value) ? new Identifier('string') : new Identifier('int');
            // constant to cases
            foreach ($constants as $constant) {
                $enum->stmts[] = $this->createEnumCaseFromConst($constant);
            }
        }
        return $enum;
    }
    public function createFromSpatieClass(Class_ $class) : Enum_
    {
        $shortClassName = $this->nodeNameResolver->getShortName($class);
        $enum = new Enum_($shortClassName, [], ['startLine' => $class->getStartLine(), 'endLine' => $class->getEndLine()]);
        $enum->namespacedName = $class->namespacedName;
        // constant to cases
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
        $docBlockMethods = $phpDocInfo->getTagsByName('@method');
        if ($docBlockMethods !== []) {
            $enum->scalarType = new Identifier('string');
            foreach ($docBlockMethods as $docBlockMethod) {
                $enum->stmts[] = $this->createEnumCaseFromDocComment($docBlockMethod);
            }
        }
        return $enum;
    }
    private function createEnumCaseFromConst(ClassConst $classConst) : EnumCase
    {
        $constConst = $classConst->consts[0];
        $enumCase = new EnumCase($constConst->name, $constConst->value);
        // mirror comments
        $enumCase->setAttribute(AttributeKey::PHP_DOC_INFO, $classConst->getAttribute(AttributeKey::PHP_DOC_INFO));
        $enumCase->setAttribute(AttributeKey::COMMENTS, $classConst->getAttribute(AttributeKey::COMMENTS));
        return $enumCase;
    }
    private function createEnumCaseFromDocComment(PhpDocTagNode $phpDocTagNode) : EnumCase
    {
        /** @var MethodTagValueNode $nodeValue */
        $nodeValue = $phpDocTagNode->value;
        $enumName = \strtoupper($nodeValue->methodName);
        $enumExpr = $this->builderFactory->val($nodeValue->methodName);
        return new EnumCase($enumName, $enumExpr);
    }
}
