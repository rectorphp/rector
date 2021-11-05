<?php

declare(strict_types=1);

namespace Rector\NameImporting\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

final class NameAndParent
{
    /**
     * @param Name|Identifier $nameNode
     */
    public function __construct(
        private string $shortName,
        private Node $nameNode,
        private Node $parentNode
    ) {
    }

    public function getNameNode(): Identifier | Name
    {
        return $this->nameNode;
    }

    public function getParentNode(): Node
    {
        return $this->parentNode;
    }

    public function matchShortName(string $desiredShortName): bool
    {
        return strtolower($this->shortName) === strtolower($desiredShortName);
    }
}
