<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

final class NameAndParentValueObject
{
    /**
     * @var Name|Identifier
     */
    private $nameNode;

    /**
     * @var Node
     */
    private $parentNode;

    /**
     * @param Name|Identifier $nameNode
     */
    public function __construct(Node $nameNode, Node $parentNode)
    {
        $this->nameNode = $nameNode;
        $this->parentNode = $parentNode;
    }

    /**
     * @return Name|Identifier
     */
    public function getNameNode(): Node
    {
        return $this->nameNode;
    }

    public function getParentNode(): Node
    {
        return $this->parentNode;
    }
}
