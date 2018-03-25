<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PHPUnit\Framework\TestCase;
use Rector\NodeTypeResolver\NodeTypeResolver;

abstract class AbstractPHPUnitRector extends AbstractRector
{
    protected function isInTestClass(Node $node): bool
    {
        $nodeTypeResolver = new NodeTypeResolver();

        $nodeResolved = $nodeTypeResolver->resolve($node);

        return (bool) ! array_intersect([TestCase::class, 'PHPUnit_Framework_TestCase'], $nodeResolved);
    }
}
