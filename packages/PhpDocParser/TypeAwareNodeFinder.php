<?php

declare (strict_types=1);
namespace Rector\PhpDocParser;

use PhpParser\Node;
use PhpParser\NodeFinder;
/**
 * @todo remove after https://github.com/nikic/PHP-Parser/pull/869 is released
 * @api
 */
final class TypeAwareNodeFinder
{
    /**
     * @readonly
     * @var \PhpParser\NodeFinder
     */
    private $nodeFinder;
    public function __construct()
    {
        // to avoid duplicated services on inject
        $this->nodeFinder = new NodeFinder();
    }
    /**
     * @template TNode as Node
     *
     * @param mixed[]|\PhpParser\Node $nodes
     * @param class-string<TNode> $type
     * @return TNode|null
     */
    public function findFirstInstanceOf($nodes, string $type) : ?Node
    {
        return $this->nodeFinder->findFirstInstanceOf($nodes, $type);
    }
    /**
     * @template TNode as Node
     *
     * @param mixed[]|\PhpParser\Node $nodes
     * @param class-string<TNode> $type
     * @return TNode[]
     */
    public function findInstanceOf($nodes, string $type) : array
    {
        return $this->nodeFinder->findInstanceOf($nodes, $type);
    }
}
