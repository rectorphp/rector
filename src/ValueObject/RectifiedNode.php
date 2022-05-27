<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject;

use PhpParser\Node;
final class RectifiedNode
{
    /**
     * @readonly
     * @var string
     */
    private $rectorClass;
    /**
     * @readonly
     * @var \PhpParser\Node
     */
    private $node;
    public function __construct(string $rectorClass, \PhpParser\Node $node)
    {
        $this->rectorClass = $rectorClass;
        $this->node = $node;
    }
    public function getRectorClass() : string
    {
        return $this->rectorClass;
    }
    public function getNode() : \PhpParser\Node
    {
        return $this->node;
    }
}
