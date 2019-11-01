<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Stmt;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\While_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/phpstan/phpstan/blob/83078fe308a383c618b8c1caec299e5765d9ac82/src/Node/UnreachableStatementNode.php
 *
 * @see \Rector\DeadCode\Tests\Rector\Stmt\RemoveUnreachableStatementRector\RemoveUnreachableStatementRectorTest
 */
final class RemoveUnreachableStatementRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unreachable statements', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        return 5;

        $removeMe = 10;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
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
        return [Stmt::class];
    }

    /**
     * @param Stmt $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Nop) {
            return null;
        }

        // might be PHPStan false positive, better skip
        $previousStatement = $node->getAttribute(AttributeKey::PREVIOUS_STATEMENT);
        if ($previousStatement instanceof If_) {
            $node->setAttribute(
                AttributeKey::IS_UNREACHABLE,
                $previousStatement->getAttribute(AttributeKey::IS_UNREACHABLE)
            );
        }

        if ($previousStatement instanceof Stmt\While_) {
            $node->setAttribute(
                AttributeKey::IS_UNREACHABLE,
                $previousStatement->getAttribute(AttributeKey::IS_UNREACHABLE)
            );
        }

        if (! $this->isUnreachable($node)) {
            return null;
        }

        if ($previousNode instanceof While_) {
            $node->setAttribute(AttributeKey::IS_UNREACHABLE, false);
            return null;
        }

        if ($this->isAfterMarkTestSkippedMethodCall($node)) {
            $node->setAttribute(AttributeKey::IS_UNREACHABLE, false);
            return null;
        }

        $this->removeNode($node);

        return null;
    }

    private function isUnreachable(Node $node): bool
    {
        $isUnreachable = $node->getAttribute(AttributeKey::IS_UNREACHABLE);
        if ($isUnreachable === true) {
            return true;
        }

        // traverse up for unreachable node in the same scope
        $previousNode = $node->getAttribute(AttributeKey::PREVIOUS_STATEMENT);

        while ($previousNode instanceof Node && ! $this->isBreakingScopeNode($previousNode)) {
            $isUnreachable = $previousNode->getAttribute(AttributeKey::IS_UNREACHABLE);
            if ($isUnreachable === true) {
                return true;
            }

            $previousNode = $previousNode->getAttribute(AttributeKey::PREVIOUS_STATEMENT);
        }

        return false;
    }

    /**
     * Keep content after markTestSkipped(), intentional temporary
     */
    private function isAfterMarkTestSkippedMethodCall(Node $node): bool
    {
        return (bool) $this->betterNodeFinder->findFirstPrevious($node, function (Node $node): bool {
            if (! $node instanceof MethodCall) {
                return false;
            }

            return $this->isName($node->name, 'markTestSkipped');
        });
    }

    /**
     * Check nodes that breaks scope while traversing up
     */
    private function isBreakingScopeNode(Node $node): bool
    {
        if ($node instanceof ClassLike) {
            return true;
        }

        if ($node instanceof ClassMethod) {
            return true;
        }

        if ($node instanceof Namespace_) {
            return true;
        }

        return $node instanceof Else_;
    }
}
