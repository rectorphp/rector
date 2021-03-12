<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node\Scalar\String_;
use PhpParser\NodeFinder;

/**
 * @template T as Node
 */
final class SkipReturnTypeTemplate
{
    /**
     * @var NodeFinder
     */
    private $nodeFinder;

    public function __construct(NodeFinder $nodeFinder)
    {
        $this->nodeFinder = $nodeFinder;
    }

    /**
     * @return T[]
     */
    public function isValidDataProvider($nodes): array
    {
        return $this->nodeFinder->findInstanceOf($nodes, String_::class);
    }
}
