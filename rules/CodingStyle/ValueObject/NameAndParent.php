<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
final class NameAndParent
{
    /**
     * @var \PhpParser\Node\Name|\PhpParser\Node\Identifier
     */
    private $nameNode;
    /**
     * @var \PhpParser\Node
     */
    private $parentNode;
    /**
     * @param Name|Identifier $nameNode
     */
    public function __construct(\PhpParser\Node $nameNode, \PhpParser\Node $parentNode)
    {
        $this->nameNode = $nameNode;
        $this->parentNode = $parentNode;
    }
    /**
     * @return \PhpParser\Node\Identifier|\PhpParser\Node\Name
     */
    public function getNameNode()
    {
        return $this->nameNode;
    }
    public function getParentNode() : \PhpParser\Node
    {
        return $this->parentNode;
    }
}
