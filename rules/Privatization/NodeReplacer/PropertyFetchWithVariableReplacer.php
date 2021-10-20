<?php

declare (strict_types=1);
namespace Rector\Privatization\NodeReplacer;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class PropertyFetchWithVariableReplacer
{
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param array<string, string[]> $methodsByPropertyName
     */
    public function replacePropertyFetchesByVariable(\PhpParser\Node\Stmt\Class_ $class, array $methodsByPropertyName) : void
    {
        foreach ($methodsByPropertyName as $propertyName => $methodNames) {
            $methodName = $methodNames[0];
            $classMethod = $class->getMethod($methodName);
            if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
                continue;
            }
            $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->getStmts(), function (\PhpParser\Node $node) use($propertyName) : ?Variable {
                if (!$node instanceof \PhpParser\Node\Expr\PropertyFetch) {
                    return null;
                }
                if (!$this->nodeNameResolver->isName($node, $propertyName)) {
                    return null;
                }
                return new \PhpParser\Node\Expr\Variable($propertyName);
            });
        }
    }
}
