<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\FunctionLike;
use RectorPrefix20220606\PhpParser\Node\Stmt\InlineHTML;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
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
