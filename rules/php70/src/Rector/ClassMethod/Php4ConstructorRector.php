<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://wiki.php.net/rfc/remove_php4_constructors
 * @see \Rector\Php70\Tests\Rector\ClassMethod\Php4ConstructorRector\Php4ConstructorRectorTest
 */
final class Php4ConstructorRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes PHP 4 style constructor to __construct.',
            [
                new CodeSample(
                    <<<'PHP'
class SomeClass
{
    public function SomeClass()
    {
    }
}
PHP
                    ,
                    <<<'PHP'
class SomeClass
{
    public function __construct()
    {
    }
}
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return null;
        }

        // process parent call references first
        $this->processClassMethodStatementsForParentConstructorCalls($node);

        // not PSR-4 constructor
        if (! $this->isName($classLike, $this->getName($node))) {
            return null;
        }

        // does it already have a __construct method?
        if ($classLike->getMethod(MethodName::CONSTRUCT) === null) {
            $node->name = new Identifier(MethodName::CONSTRUCT);
        }

        if ($node->stmts === null) {
            return null;
        }

        if (count($node->stmts) === 1) {
            /** @var Expression $stmt */
            $stmt = $node->stmts[0];

            if ($this->isLocalMethodCallNamed($stmt->expr, MethodName::CONSTRUCT)) {
                $this->removeNode($node);

                return null;
            }
        }

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        $namespace = $classMethod->getAttribute(AttributeKey::NAMESPACE_NAME);
        // catch only classes without namespace
        if ($namespace !== null) {
            return true;
        }

        if ($classMethod->isAbstract() || $classMethod->isStatic()) {
            return true;
        }

        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        return $classLike instanceof Class_ && $classLike->name === null;
    }

    private function processClassMethodStatementsForParentConstructorCalls(ClassMethod $classMethod): void
    {
        if (! is_iterable($classMethod->stmts)) {
            return;
        }

        /** @var Expression $methodStmt */
        foreach ($classMethod->stmts as $methodStmt) {
            if ($methodStmt instanceof Expression) {
                $methodStmt = $methodStmt->expr;
            }

            if (! $methodStmt instanceof StaticCall) {
                continue;
            }

            $this->processParentPhp4ConstructCall($methodStmt);
        }
    }

    private function processParentPhp4ConstructCall(Node $node): void
    {
        $parentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);

        if (! $node instanceof StaticCall) {
            return;
        }

        // no parent class
        if (! is_string($parentClassName)) {
            return;
        }

        if (! $node->class instanceof Name) {
            return;
        }

        // rename ParentClass
        if ($this->isName($node->class, $parentClassName)) {
            $node->class = new Name('parent');
        }

        if (! $this->isName($node->class, 'parent')) {
            return;
        }

        // it's not a parent PHP 4 constructor call
        if (! $this->isName($node->name, $parentClassName)) {
            return;
        }

        $node->name = new Identifier(MethodName::CONSTRUCT);
    }
}
