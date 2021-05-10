<?php

declare(strict_types=1);

namespace Rector\DeadCode\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class VariableNodeUse
{
    /**
     * @var string
     */
    public const TYPE_USE = 'use';

    /**
     * @var string
     */
    public const TYPE_ASSIGN = 'assign';

    public function __construct(
        private int $startTokenPosition,
        private string $variableName,
        private string $type,
        private Variable $variable,
        private ?string $nestingHash = null
    ) {
    }

    public function isName(string $name): bool
    {
        return $this->variableName === $name;
    }

    public function getStartTokenPosition(): int
    {
        return $this->startTokenPosition;
    }

    public function isType(string $type): bool
    {
        return $this->type === $type;
    }

    public function getVariableNode(): Variable
    {
        return $this->variable;
    }

    public function getParentNode(): Node
    {
        $parentNode = $this->variable->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Node) {
            throw new ShouldNotHappenException();
        }

        return $parentNode;
    }

    public function getNestingHash(): ?string
    {
        return $this->nestingHash;
    }
}
