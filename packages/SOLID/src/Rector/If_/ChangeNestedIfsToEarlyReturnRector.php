<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Exception\NotImplementedException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Manipulator\BinaryOpManipulator;
use Rector\PhpParser\Node\Manipulator\IfManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\SOLID\Tests\Rector\If_\ChangeNestedIfsToEarlyReturnRector\ChangeNestedIfsToEarlyReturnRectorTest
 */
final class ChangeNestedIfsToEarlyReturnRector extends AbstractRector
{
    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    /**
     * @var BinaryOpManipulator
     */
    private $binaryOpManipulator;

    public function __construct(IfManipulator $ifManipulator, BinaryOpManipulator $binaryOpManipulator)
    {
        $this->ifManipulator = $ifManipulator;
        $this->binaryOpManipulator = $binaryOpManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change nested ifs to early return', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        if ($value === 5) {
            if ($value2 === 10) {
                return 'yes';
            }
        }

        return 'no';
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        if ($value !== 5) {
            return 'no';
        }

        if ($value2 === 10) {
            return 'yes';
        }

        return 'no';
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
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // A. next node is return
        $nextNode = $node->getAttribute(AttributeKey::NEXT_NODE);
        if (! $nextNode instanceof Return_) {
            return null;
        }

        $nestedIfsWithOnlyReturn = $this->ifManipulator->collectNestedIfsWithOnlyReturn($node);
        if ($nestedIfsWithOnlyReturn === []) {
            return null;
        }

        $this->processNestedIfsWIthOnlyReturn($node, $nestedIfsWithOnlyReturn, $nextNode);
        $this->removeNode($node);

        return null;
    }

    /**
     * @param If_[] $nestedIfsWithOnlyReturn
     */
    private function processNestedIfsWIthOnlyReturn(If_ $if, array $nestedIfsWithOnlyReturn, Return_ $nextReturn): void
    {
        // add nested if openly after this
        $nestedIfsWithOnlyReturnCount = count($nestedIfsWithOnlyReturn);

        /** @var int $key */
        foreach ($nestedIfsWithOnlyReturn as $key => $nestedIfWithOnlyReturn) {
            // last item → the return node
            if ($nestedIfsWithOnlyReturnCount === $key + 1) {
                $this->addNodeAfterNode($nestedIfWithOnlyReturn, $if);
            } else {
                if (! $nestedIfWithOnlyReturn->cond instanceof BinaryOp) {
                    throw new NotImplementedException(__METHOD__);
                }

                $inversedCondition = $this->binaryOpManipulator->inverseCondition($nestedIfWithOnlyReturn->cond);
                if ($inversedCondition === null) {
                    throw new NotImplementedException(__METHOD__);
                }

                $nestedIfWithOnlyReturn->cond = $inversedCondition;
                $nestedIfWithOnlyReturn->stmts = [clone $nextReturn];

                $this->addNodeAfterNode($nestedIfWithOnlyReturn, $if);
            }
        }
    }
}
