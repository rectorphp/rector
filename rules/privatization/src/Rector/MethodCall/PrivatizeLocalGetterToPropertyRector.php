<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Privatization\Tests\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector\PrivatizeLocalGetterToPropertyRectorTest
 */
final class PrivatizeLocalGetterToPropertyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Privatize getter of local property to property', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    private $some;

    public function run()
    {
        return $this->getSome() + 5;
    }

    private function getSome()
    {
        return $this->some;
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    private $some;

    public function run()
    {
        return $this->some + 5;
    }

    private function getSome()
    {
        return $this->some;
    }
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isVariableName($node->var, 'this')) {
            return null;
        }

        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return null;
        }

        $methodName = $this->getName($node->name);
        if ($methodName === null) {
            return null;
        }

        /** @var ClassMethod|null $classMethod */
        $classMethod = $class->getMethod($methodName);
        if ($classMethod === null) {
            return null;
        }

        return $this->matchLocalPropertyFetchInGetterMethod($classMethod);
    }

    private function matchLocalPropertyFetchInGetterMethod(ClassMethod $classMethod): ?PropertyFetch
    {
        if (count((array) $classMethod->getStmts()) !== 1) {
            return null;
        }

        $onlyStmt = $classMethod->stmts[0];
        if (! $onlyStmt instanceof Return_) {
            return null;
        }

        if (! $onlyStmt->expr instanceof PropertyFetch) {
            return null;
        }

        return $onlyStmt->expr;
    }
}
