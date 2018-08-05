<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Exception\ShouldNotHappenException;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;

abstract class AbstractPHPUnitRector extends AbstractRector
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * Nasty magic, unable to do that in config autowire _instanceof calls.
     *
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
            throw new ShouldNotHappenException(sprintf(
                '"%s" should be set in "%s"',
                Attribute::CLASS_NODE,
                __METHOD__
            ));
        }

        $nodeTypes = $this->nodeTypeResolver->resolve($classNode);

        return (bool) array_intersect(['PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase'], $nodeTypes);
    }
}
