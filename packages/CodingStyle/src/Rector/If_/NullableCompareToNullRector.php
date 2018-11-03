<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Stmt\If_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class NullableCompareToNullRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes negate of empty comparison of nullable value to explicit === or !== compare',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
/** @var stdClass|null $value */
if ($value) { 
}

if (!$value) {
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
/** @var stdClass|null $value */
if ($value !== null) {
}

if ($value === null) {
}
CODE_SAMPLE
                ),
            ]
        );
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
        if ($this->isNullableType($node->cond)) {
            $node->cond = new NotIdentical($node->cond, $this->createNull());

            return $node;
        }

        if ($node->cond instanceof BooleanNot && $this->isNullableType($node->cond->expr)) {
            $node->cond = new Identical($node->cond->expr, $this->createNull());

            return $node;
        }

        return null;
    }
}
