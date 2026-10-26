<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Reflection\ClassReflection;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
use Rector\ValueObject\PhpVersion;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnVendorLockResolver;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassLike\AddVoidReturnTypeWhereNoReturnRector\AddVoidReturnTypeWhereNoReturnRectorTest
 */
final class AddVoidReturnTypeWhereNoReturnRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private SilentVoidResolver $silentVoidResolver;
    /**
     * @readonly
     */
    private ClassMethodReturnVendorLockResolver $classMethodReturnVendorLockResolver;
    public function __construct(SilentVoidResolver $silentVoidResolver, ClassMethodReturnVendorLockResolver $classMethodReturnVendorLockResolver)
    {
        $this->silentVoidResolver = $silentVoidResolver;
        $this->classMethodReturnVendorLockResolver = $classMethodReturnVendorLockResolver;
    }
    public function getRuleDefinition(): RuleDefinition
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
    public function getNodeTypes(): array
    {
        return [ClassLike::class];
    }
    /**
     * @param ClassLike $node
     */
    public function refactor(Node $node): ?Node
    {
        $classReflection = $this->fetchClassLikeReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $isFinalClass = $classReflection->isFinalByKeyword();
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            // already has return type → skip
            if ($classMethod->returnType instanceof Node) {
                continue;
            }
            if ($this->shouldSkipClassMethod($classMethod, $classReflection, $isFinalClass)) {
                continue;
            }
            if (!$this->silentVoidResolver->hasExclusiveVoid($classMethod)) {
                continue;
            }
            if ($this->classMethodReturnVendorLockResolver->isVendorLocked($classMethod)) {
                continue;
            }
            $classMethod->returnType = new Identifier('void');
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_71;
    }
    private function shouldSkipClassMethod(ClassMethod $classMethod, ClassReflection $classReflection, bool $isFinalClass): bool
    {
        if ($classMethod->isAbstract()) {
            return \true;
        }
        // is not final and has only exception? possibly implemented by child
        if (!$isFinalClass && $this->hasExceptionOnly($classMethod)) {
            return \true;
        }
        // possibly required by child implementation
        if (!$isFinalClass && $classMethod->stmts === []) {
            return \true;
        }
        if ($classMethod->isProtected()) {
            return !$isFinalClass;
        }
        return $classReflection->isAbstract();
    }
    private function hasExceptionOnly(ClassMethod $classMethod): bool
    {
        if (count((array) $classMethod->stmts) !== 1) {
            return \false;
        }
        $onlyStmt = $classMethod->stmts[0] ?? null;
        return $onlyStmt instanceof Expression && $onlyStmt->expr instanceof Throw_;
    }
}
