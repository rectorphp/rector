<?php

declare (strict_types=1);
namespace Rector\Naming\PropertyRenamer;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\VarLikeIdentifier;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220501\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class PropertyFetchRenamer
{
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\RectorPrefix20220501\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function renamePropertyFetchesInClass(\PhpParser\Node\Stmt\ClassLike $classLike, string $currentName, string $expectedName) : void
    {
        // 1. replace property fetch rename in whole class
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classLike, function (\PhpParser\Node $node) use($currentName, $expectedName) : ?Node {
            if ($node instanceof \PhpParser\Node\Expr\PropertyFetch && $this->nodeNameResolver->isLocalPropertyFetchNamed($node, $currentName)) {
                $node->name = new \PhpParser\Node\Identifier($expectedName);
                return $node;
            }
            if (!$node instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->name, $currentName)) {
                return null;
            }
            $node->name = new \PhpParser\Node\VarLikeIdentifier($expectedName);
            return $node;
        });
    }
}
