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
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function hasInlineHTML(Node $node) : bool
    {
        if ($node instanceof FunctionLike) {
            return (bool) $this->betterNodeFinder->findInstanceOf((array) $node->getStmts(), InlineHTML::class);
        }
        return (bool) $this->betterNodeFinder->findInstanceOf($node, InlineHTML::class);
    }
}
