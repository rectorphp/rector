<?php

declare(strict_types=1);

namespace Rector\Generic\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use Rector\Core\Rector\AbstractRector;
use Rector\Php71\IsArrayAndDualCheckToAble;

abstract class AbstractIsAbleRector extends AbstractRector
{
    /**
     * @var IsArrayAndDualCheckToAble
     */
    private $isArrayAndDualCheckToAble;

    public function __construct(IsArrayAndDualCheckToAble $isArrayAndDualCheckToAble)
    {
        $this->isArrayAndDualCheckToAble = $isArrayAndDualCheckToAble;
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

        return $this->isArrayAndDualCheckToAble->processBooleanOr(
            $node,
            $this->getType(),
            $this->getFuncName()
        ) ?: $node;
    }

    abstract protected function getFuncName(): string;

    abstract protected function getPhpVersion(): string;

    abstract protected function getType(): string;

    private function shouldSkip(): bool
    {
        if (function_exists($this->getFuncName())) {
            return false;
        }

        return $this->isAtLeastPhpVersion($this->getPhpVersion());
    }
}
