<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Privatization\NodeReplacer;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class PropertyFetchWithVariableReplacer
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
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param array<string, string[]> $methodsByPropertyName
     */
    public function replacePropertyFetchesByVariable(Class_ $class, array $methodsByPropertyName) : void
    {
        foreach ($methodsByPropertyName as $propertyName => $methodNames) {
            $methodName = $methodNames[0];
            $classMethod = $class->getMethod($methodName);
            if (!$classMethod instanceof ClassMethod) {
                continue;
            }
            $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->getStmts(), function (Node $node) use($propertyName) : ?Variable {
                if (!$node instanceof PropertyFetch) {
                    return null;
                }
                if (!$this->nodeNameResolver->isName($node, $propertyName)) {
                    return null;
                }
                return new Variable($propertyName);
            });
        }
    }
}
