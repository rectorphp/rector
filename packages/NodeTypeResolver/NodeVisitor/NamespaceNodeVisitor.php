<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class NamespaceNodeVisitor extends \PhpParser\NodeVisitorAbstract
{
    /**
     * @var Use_[]
     */
    private $useNodes = [];
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @param Node[] $nodes
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes) : ?array
    {
        // init basic use nodes for non-namespaced code
        /** @var Use_[] $uses */
        $uses = $this->betterNodeFinder->findInstanceOf($nodes, \PhpParser\Node\Stmt\Use_::class);
        $this->useNodes = $uses;
        return null;
    }
    public function enterNode(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
            /** @var Use_[] $uses */
            $uses = $this->betterNodeFinder->findInstanceOf($node, \PhpParser\Node\Stmt\Use_::class);
            $this->useNodes = $uses;
        }
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::USE_NODES, $this->useNodes);
        return $node;
    }
}
