<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Expr;

final class ConcatExpressionJoinData
{
    /**
     * @var string[]
     */
    private array $values = [];

    /**
     * @var Node[]
     */
    private array $nodesToRemove = [];

    /**
     * @var Expr[]
     */
    private array $placeholdersToNodes = [];

    public function addString(string $value): void
    {
        $this->values[] = $value;
    }

    public function addNodeToRemove(Node $node): void
    {
        $this->nodesToRemove[] = $node;
    }

    public function getString(): string
    {
        return implode('', $this->values);
    }

    /**
     * @return Node[]
     */
    public function getNodesToRemove(): array
    {
        return $this->nodesToRemove;
    }

    public function addPlaceholderToNode(string $objectHash, Expr $expr): void
    {
        $this->placeholdersToNodes[$objectHash] = $expr;
    }

    /**
     * @return Expr[]
     */
    public function getPlaceholdersToNodes(): array
    {
        return $this->placeholdersToNodes;
    }
}
