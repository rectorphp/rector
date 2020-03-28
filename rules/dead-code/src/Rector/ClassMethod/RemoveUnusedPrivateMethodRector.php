<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeCollector\NodeFinder\MethodCallParsedNodesFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\RemoveUnusedPrivateMethodRectorTest
 */
final class RemoveUnusedPrivateMethodRector extends AbstractRector
{
    /**
     * @var MethodCallParsedNodesFinder
     */
    private $methodCallParsedNodesFinder;

    public function __construct(MethodCallParsedNodesFinder $methodCallParsedNodesFinder)
    {
        $this->methodCallParsedNodesFinder = $methodCallParsedNodesFinder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unused private method', [
            new CodeSample(
                <<<'PHP'
final class SomeController
{
    public function run()
    {
        return 5;
    }

    private function skip()
    {
        return 10;
    }
}
PHP
                ,
                <<<'PHP'
final class SomeController
{
    public function run()
    {
        return 5;
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

        $classMethodCalls = $this->methodCallParsedNodesFinder->findClassMethodCalls($node);
        if ($classMethodCalls !== []) {
            return null;
        }

        $this->removeNode($node);

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        /** @var Class_|Interface_|Trait_|null $classNode */
        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return true;
        }

        // unreliable to detect trait, interface doesn't make sense
        if ($classNode instanceof Trait_ || $classNode instanceof Interface_) {
            return true;
        }

        if ($this->isAnonymousClass($classNode)) {
            return true;
        }

        // skips interfaces by default too
        if (! $classMethod->isPrivate()) {
            return true;
        }

        // skip magic methods - @see https://www.php.net/manual/en/language.oop5.magic.php
        return $this->isName($classMethod, '__*');
    }
}
