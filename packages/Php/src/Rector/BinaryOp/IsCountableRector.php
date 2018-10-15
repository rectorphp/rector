<?php declare(strict_types=1);

namespace Rector\Php\Rector\BinaryOp;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use Rector\Php\DualCheckToAble;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class IsCountableRector extends AbstractRector
{
    /**
     * @var DualCheckToAble
     */
    private $dualCheckToAble;

    public function __construct(DualCheckToAble $dualCheckToAble)
    {
        $this->dualCheckToAble = $dualCheckToAble;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes is_array + Countable check to is_countable',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
is_array($foo) || $foo instanceof Countable;
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
is_countable($foo);
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
        return [BooleanOr::class];
    }

    /**
     * @param BooleanOr $node
     */
    public function refactor(Node $node): ?Node
    {
        return $this->dualCheckToAble->processBooleanOr($node, 'Countable', 'is_countable') ?: $node;
    }
}
