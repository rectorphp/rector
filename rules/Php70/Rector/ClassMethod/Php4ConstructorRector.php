<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php70\NodeAnalyzer\Php4ConstructorClassMethodAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/remove_php4_constructors
 * @see \Rector\Tests\Php70\Rector\ClassMethod\Php4ConstructorRector\Php4ConstructorRectorTest
 */
final class Php4ConstructorRector extends AbstractRector
{
    public function __construct(
        private Php4ConstructorClassMethodAnalyzer $php4ConstructorClassMethodAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes PHP 4 style constructor to __construct.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function SomeClass()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct()
    {
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->php4ConstructorClassMethodAnalyzer->detect($node)) {
            return null;
        }

        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return null;
        }

        // process parent call references first
        $this->processClassMethodStatementsForParentConstructorCalls($node);

        // not PSR-4 constructor
        if (! $this->nodeNameResolver->areNamesEqual($classLike, $node)) {
            return null;
        }

        $classMethod = $classLike->getMethod(MethodName::CONSTRUCT);

        // does it already have a __construct method?
        if (! $classMethod instanceof ClassMethod) {
            $node->name = new Identifier(MethodName::CONSTRUCT);
        }

        if ($node->stmts === null) {
            return null;
        }

        if (count($node->stmts) === 1) {
            /** @var Expression|Expr $stmt */
            $stmt = $node->stmts[0];
            if (! $stmt instanceof Expression) {
                return null;
            }

            if ($this->isLocalMethodCallNamed($stmt->expr, MethodName::CONSTRUCT)) {
                $this->removeNode($node);

                return null;
            }
        }

        return $node;
    }

    private function processClassMethodStatementsForParentConstructorCalls(ClassMethod $classMethod): void
    {
        if (! is_iterable($classMethod->stmts)) {
            return;
        }

        foreach ($classMethod->stmts as $methodStmt) {
            if (! $methodStmt instanceof Expression) {
                continue;
            }

            $methodStmt = $methodStmt->expr;
            if (! $methodStmt instanceof StaticCall) {
                continue;
            }

            $this->processParentPhp4ConstructCall($methodStmt);
        }
    }

    private function processParentPhp4ConstructCall(StaticCall $staticCall): void
    {
        $parentClassName = $this->resolveParentClassName($staticCall);

        // no parent class
        if ($parentClassName === null) {
            return;
        }

        if (! $staticCall->class instanceof Name) {
            return;
        }

        // rename ParentClass
        if ($this->isName($staticCall->class, $parentClassName)) {
            $staticCall->class = new Name('parent');
        }

        if (! $this->isName($staticCall->class, 'parent')) {
            return;
        }

        // it's not a parent PHP 4 constructor call
        if (! $this->isName($staticCall->name, $parentClassName)) {
            return;
        }

        $staticCall->name = new Identifier(MethodName::CONSTRUCT);
    }

    private function resolveParentClassName(StaticCall $staticCall): ?string
    {
        $scope = $staticCall->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }
        $classReflection = $scope->getClassReflection();

        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        $parentClassReflection = $classReflection->getParentClass();
        if (! $parentClassReflection instanceof ClassReflection) {
            return null;
        }

        return $parentClassReflection->getName();
    }

    private function isLocalMethodCallNamed(Expr $expr, string $name): bool
    {
        if (! $expr instanceof MethodCall) {
            return false;
        }

        if ($expr->var instanceof StaticCall) {
            return false;
        }

        if ($expr->var instanceof MethodCall) {
            return false;
        }

        if (! $this->isName($expr->var, 'this')) {
            return false;
        }

        return $this->isName($expr->name, $name);
    }
}
