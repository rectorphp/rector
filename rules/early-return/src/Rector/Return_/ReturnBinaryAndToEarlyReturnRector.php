<?php

declare(strict_types=1);

namespace Rector\EarlyReturn\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\ReturnBinaryAndToEarlyReturnRectorTest
 */
final class ReturnBinaryAndToEarlyReturnRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes Single return of && && to early returns', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function accept($something, $somethingelse)
    {
        return $something && $somethingelse;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function accept($something, $somethingelse)
    {
        if (!$something) {
            return false;
        }
        return (bool) $somethingelse;
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
        if (! $node->expr instanceof BooleanAnd) {
            return null;
        }

        $if = new If_(
            new BooleanNot($node->expr->left),
            [
                'stmts' => [new Return_($this->createFalse())],
            ]
        );
        $next = $node->expr->right instanceof Bool_ ? $node->expr->right : new Bool_($node->expr->right);
        $this->addNodeAfterNode(new Return_($next), $if);
        $this->removeNode($node);

        return $if;
    }
}
