<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\Contract;

use PhpParser\Node;
use PHPStan\Type\Type;
/**
 * @template TNode as Node
 */
interface NodeTypeResolverInterface
{
    /**
     * @return array<class-string<TNode>>
     */
    public function getNodeClasses() : array;
    /**
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : \PHPStan\Type\Type;
}
