<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PHPUnit\Framework\TestCase;
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
        $nodeResolved = $this->nodeTypeResolver->resolve($node);

        return ! array_intersect([TestCase::class, 'PHPUnit_Framework_TestCase'], $nodeResolved);
    }
}
