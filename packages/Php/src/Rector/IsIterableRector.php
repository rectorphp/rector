<?php declare(strict_types=1);

namespace Rector\Php\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use Rector\Php\DualCheckToAble;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class IsIterableRector extends AbstractRector
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
            'Changes is_array + Traversable check to is_iterable',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
is_array($foo) || $foo instanceof Traversable;
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
is_iterable($foo);
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
     * @param BooleanOr $booleanOrNode
     */
    public function refactor(Node $booleanOrNode): ?Node
    {
        return $this->dualCheckToAble->processBooleanOr($booleanOrNode, 'Traversable', 'is_iterable') ?: $booleanOrNode;
    }
}
