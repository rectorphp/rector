<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;

abstract class AbstractPHPUnitRector extends AbstractRector
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @required
     */
    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    protected function isInTestClass(Node $node): bool
    {
        /** @var Class_|null $classNode */
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        if ($classNode === null) {
            return false;
        }

        $nodeTypes = $this->nodeTypeResolver->resolve($classNode);

        return (bool) array_intersect(['PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase'], $nodeTypes);
    }
}
