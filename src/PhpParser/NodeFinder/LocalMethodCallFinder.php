<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
final class LocalMethodCallFinder
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return MethodCall[]|StaticCall[]
     */
    public function match(Class_ $class, ClassMethod $classMethod) : array
    {
        $className = $this->nodeNameResolver->getName($class);
        if (!\is_string($className)) {
            return [];
        }
        $classMethodName = $this->nodeNameResolver->getName($classMethod);
        /** @var MethodCall[]|StaticCall[] $matchingMethodCalls */
        $matchingMethodCalls = $this->betterNodeFinder->find($class->getMethods(), function (Node $subNode) use($className, $classMethodName) : bool {
            if (!$subNode instanceof MethodCall && !$subNode instanceof StaticCall) {
                return \false;
            }
            if (!$this->nodeNameResolver->isName($subNode->name, $classMethodName)) {
                return \false;
            }
            $callerType = $subNode instanceof MethodCall ? $this->nodeTypeResolver->getType($subNode->var) : $this->nodeTypeResolver->getType($subNode->class);
            return ClassNameFromObjectTypeResolver::resolve($callerType) === $className;
        });
        return $matchingMethodCalls;
    }
}
