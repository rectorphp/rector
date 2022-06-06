<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\Contract;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PHPStan\Type\Type;
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
