<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\InlineHTML;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
final class InlineHTMLAnalyzer
{
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function hasInlineHTML(\PhpParser\Node $node) : bool
    {
        if ($node instanceof \PhpParser\Node\FunctionLike) {
            return (bool) $this->betterNodeFinder->findInstanceOf((array) $node->getStmts(), \PhpParser\Node\Stmt\InlineHTML::class);
        }
        return (bool) $this->betterNodeFinder->findInstanceOf($node, \PhpParser\Node\Stmt\InlineHTML::class);
    }
}
