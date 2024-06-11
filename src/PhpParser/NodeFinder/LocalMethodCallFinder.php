<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
final class LocalMethodCallFinder
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
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
            if (!$callerType instanceof TypeWithClassName) {
                return \false;
            }
            return $callerType->getClassName() === $className;
        });
        return $matchingMethodCalls;
    }
}
