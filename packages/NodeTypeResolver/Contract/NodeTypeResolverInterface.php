<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\Contract;

use PhpParser\Node;
use PHPStan\Type\Type;
/**
 * @template TNode as \PhpParser\Node
 */
interface NodeTypeResolverInterface
{
    /**
     * @return array<class-string<TNode>>
     */
    public function getNodeClasses() : array;
    /**
     * @param TNode $node
     */
    public function resolve(Node $node) : Type;
}
