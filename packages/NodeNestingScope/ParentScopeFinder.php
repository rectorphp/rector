<?php

declare(strict_types=1);

namespace Rector\NodeNestingScope;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ParentScopeFinder
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

    /**
     * @return ClassMethod|Function_|Class_|Namespace_|Closure|null
     */
    public function find(Node $node): ?Node
    {
        return $node->getAttribute(AttributeKey::CLOSURE_NODE) ??
            $node->getAttribute(AttributeKey::FUNCTION_NODE) ??
            $node->getAttribute(AttributeKey::METHOD_NODE) ??
            $node->getAttribute(AttributeKey::CLASS_NODE) ??
            $this->betterNodeFinder->findParentType($node, Namespace_::class);
    }
}
