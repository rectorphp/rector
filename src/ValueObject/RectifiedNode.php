<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\ValueObject;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\Rector\Core\Contract\Rector\RectorInterface;
final class RectifiedNode
{
    /**
     * @var class-string<RectorInterface>
     * @readonly
     */
    private $rectorClass;
    /**
     * @readonly
     * @var \PhpParser\Node
     */
    private $node;
    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    public function __construct(string $rectorClass, Node $node)
    {
        $this->rectorClass = $rectorClass;
        $this->node = $node;
    }
    /**
     * @return class-string<RectorInterface>
     */
    public function getRectorClass() : string
    {
        return $this->rectorClass;
    }
    public function getNode() : Node
    {
        return $this->node;
    }
}
