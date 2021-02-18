<?php

declare(strict_types=1);

namespace Rector\EarlyReturn\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\EarlyReturn\Tests\Rector\Return_\PreparedValueToEarlyReturnRector\PreparedValueToEarlyReturnRectorTest
 */
final class PreparedValueToEarlyReturnRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Return early prepared value in ifs', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $var = null;

        if (rand(0,1)) {
            $var = 1;
        }

        if (rand(0,1)) {
            $var = 2;
        }

        return $var;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if (rand(0,1)) {
            return 1;
        }

        if (rand(0,1)) {
            return 2;
        }

        return null;
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
        return [Return_::class];
    }

    /**
     * @param Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        return $node;
    }

    private function shouldSkip(Return_ $return): bool
    {
        $ifsBefore = $this->getIfsBefore($return);
        if ($ifsBefore === []) {
            return true;
        }

        return false;
    }

    private function getIfsBefore(Return_ $return): array
    {
        $parent = $return->getAttribute(AttributeKey::PARENT_NODE);
        if (
            ($parent instanceof FunctionLike || $parent instanceof If_)
            && $parent->stmts[count($parent->stmts) - 1] === $return
        ) {
            /** @va If_ $ifs */
            $ifs = $this->betterNodeFinder->findInstanceOf($parent->stmts, If_::class);

            foreach ($ifs as $if) {
                if ($if->else instanceof Else_) {
                    return [];
                }

                if ($if->elseifs !== []) {
                    return [];
                }

                if (count($if->stmts) !== 1) {
                    return [];
                }

                if (! $if->stmts[0] instanceof Assign) {
                    return [];
                }

                if (! $this->areNodesEqual($if->stmts[0]->var, $return->expr)) {
                    return [];
                }
            }

            return $ifs;
        }

        return [];
    }
}
