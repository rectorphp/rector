<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\Contract;

use PhpParser\Node;
use PHPStan\Type\Type;
interface NodeTypeResolverInterface
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array;
    /**
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : \PHPStan\Type\Type;
}
