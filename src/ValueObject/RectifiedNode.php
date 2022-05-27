<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject;

use PhpParser\Node;
use Rector\Core\Contract\Rector\RectorInterface;
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
    public function __construct(string $rectorClass, \PhpParser\Node $node)
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
    public function getNode() : \PhpParser\Node
    {
        return $this->node;
    }
}
