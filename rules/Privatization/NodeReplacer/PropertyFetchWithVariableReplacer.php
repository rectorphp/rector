<?php

declare (strict_types=1);
namespace Rector\Privatization\NodeReplacer;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220609\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
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
