<?php declare(strict_types=1);

namespace Rector\Php\Rector\BinaryOp;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use Rector\Php\IsArrayAndDualCheckToAble;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Php\Tests\Rector\BinaryOp\IsIterableRector\IsIterableRectorTest
 */
final class IsIterableRector extends AbstractRector
{
    /**
     * @var IsArrayAndDualCheckToAble
     */
    private $isArrayAndDualCheckToAble;

    public function __construct(IsArrayAndDualCheckToAble $isArrayAndDualCheckToAble)
    {
        $this->isArrayAndDualCheckToAble = $isArrayAndDualCheckToAble;
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
     * @param BooleanOr $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip()) {
            return null;
        }

        return $this->isArrayAndDualCheckToAble->processBooleanOr($node, 'Traversable', 'is_iterable') ?: $node;
    }

    private function shouldSkip(): bool
    {
        if (function_exists('is_iterable')) {
            return false;
        }

        return $this->isAtLeastPhpVersion('7.1');
    }
}
