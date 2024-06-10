<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Throw_;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ClassModifierChecker;
use Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnVendorLockResolver;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector\AddVoidReturnTypeWhereNoReturnRectorTest
 */
final class AddVoidReturnTypeWhereNoReturnRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\SilentVoidResolver
     */
    private $silentVoidResolver;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnVendorLockResolver
     */
    private $classMethodReturnVendorLockResolver;
    /**
     * @readonly
     * @var \Rector\Reflection\ClassModifierChecker
     */
    private $classModifierChecker;
    public function __construct(SilentVoidResolver $silentVoidResolver, ClassMethodReturnVendorLockResolver $classMethodReturnVendorLockResolver, ClassModifierChecker $classModifierChecker)
    {
        $this->silentVoidResolver = $silentVoidResolver;
        $this->classMethodReturnVendorLockResolver = $classMethodReturnVendorLockResolver;
        $this->classModifierChecker = $classModifierChecker;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add return type void to function like without any return', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function getValues()
    {
        $value = 1000;
        return;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function getValues(): void
    {
        $value = 1000;
        return;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        // already has return type → skip
        if ($node->returnType instanceof Node) {
            return null;
        }
        if ($this->shouldSkipClassMethod($node)) {
            return null;
        }
        if (!$this->silentVoidResolver->hasExclusiveVoid($node)) {
            return null;
        }
        if ($this->classMethodReturnVendorLockResolver->isVendorLocked($node)) {
            return null;
        }
        $node->returnType = new Identifier('void');
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::VOID_TYPE;
    }
    private function shouldSkipClassMethod(ClassMethod $classMethod) : bool
    {
        if ($classMethod->isAbstract()) {
            return \true;
        }
        // is not final and has only exception? possibly implemented by child
        if ($this->isNotFinalAndHasExceptionOnly($classMethod)) {
            return \true;
        }
        // possibly required by child implementation
        if ($this->isNotFinalAndEmpty($classMethod)) {
            return \true;
        }
        if ($classMethod->isProtected()) {
            return !$this->classModifierChecker->isInsideFinalClass($classMethod);
        }
        return $this->classModifierChecker->isInsideAbstractClass($classMethod) && $classMethod->getStmts() === [];
    }
    private function isNotFinalAndHasExceptionOnly(ClassMethod $classMethod) : bool
    {
        if ($this->classModifierChecker->isInsideFinalClass($classMethod)) {
            return \false;
        }
        if (\count((array) $classMethod->stmts) !== 1) {
            return \false;
        }
        $onlyStmt = $classMethod->stmts[0] ?? null;
        return $onlyStmt instanceof Throw_;
    }
    private function isNotFinalAndEmpty(ClassMethod $classMethod) : bool
    {
        if ($this->classModifierChecker->isInsideFinalClass($classMethod)) {
            return \false;
        }
        return $classMethod->stmts === [];
    }
}
