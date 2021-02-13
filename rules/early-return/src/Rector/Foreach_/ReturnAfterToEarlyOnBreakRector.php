<?php

declare(strict_types=1);

namespace Rector\EarlyReturn\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\EarlyReturn\Tests\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector\ReturnAfterToEarlyOnBreakRectorTest
 */
final class ReturnAfterToEarlyOnBreakRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change return after foreach to early return in foreach on break', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(array $pathConstants, string $allowedPath)
    {
        $pathOK = false;

        foreach ($pathConstants as $allowedPath) {
            if ($dirPath == $allowedPath) {
                $pathOK = true;
                break;
            }
        }

        return $pathOK;
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(array $pathConstants, string $allowedPath)
    {
        foreach ($pathConstants as $allowedPath) {
            if ($dirPath == $allowedPath) {
                return true;
            }
        }

        return false;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Foreach_::class];
    }

    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var Break_[] $breaks */
        $breaks = $this->betterNodeFinder->findInstanceOf($node->stmts, Break_::class);
        if (count($breaks) !== 1) {
            return null;
        }

        $beforeBreak = $breaks[0]->getAttribute(AttributeKey::PREVIOUS_NODE);
        if (! $beforeBreak instanceof Expression) {
            return null;
        }

        $assign = $beforeBreak->expr;
        if (! $assign instanceof Assign) {
            return null;
        }

        $nextForeach = $node->getAttribute(AttributeKey::NEXT_NODE);
        if (! $nextForeach instanceof Return_) {
            return null;
        }

        $assignVariable = $assign->var;
        $variablePrevious = $this->betterNodeFinder->findFirstPrevious($node, function (Node $node) use (
            $assignVariable
        ): bool {
            return $this->areNodesEqual($node, $assignVariable);
        });

        dump($variablePrevious);

        return $node;
    }
}
