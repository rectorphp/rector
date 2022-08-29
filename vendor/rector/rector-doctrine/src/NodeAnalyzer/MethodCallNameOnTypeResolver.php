<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class MethodCallNameOnTypeResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @return string[]
     */
    public function resolve(Class_ $class, ObjectType $objectType) : array
    {
        $methodNames = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($class, function (Node $node) use(&$methodNames, $objectType) {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->nodeTypeResolver->isObjectType($node->var, $objectType)) {
                return null;
            }
            $name = $this->nodeNameResolver->getName($node->name);
            if ($name === null) {
                return null;
            }
            $methodNames[] = $name;
        });
        return \array_unique($methodNames);
    }
}
