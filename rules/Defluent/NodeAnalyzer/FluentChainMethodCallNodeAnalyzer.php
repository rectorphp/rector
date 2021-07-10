<?php

declare(strict_types=1);

namespace Rector\Defluent\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use PHPStan\Type\ThisType;

/**
 * Utils for chain of MethodCall Node:
 * "$this->methodCall()->chainedMethodCall()"
 */
final class FluentChainMethodCallNodeAnalyzer
{
    /**
     * Types that look like fluent interface, but actually create a new object.
     * Should be skipped, as they return different object. Not an fluent interface!
     *
     * @var class-string<MutatingScope>[]
     */
    private const KNOWN_FACTORY_FLUENT_TYPES = ['PHPStan\Analyser\MutatingScope'];

    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private NodeTypeResolver $nodeTypeResolver,
        private NodeFinder $nodeFinder,
        private AstResolver $astResolver,
        private BetterNodeFinder $betterNodeFinder,
        private NodeComparator $nodeComparator,
        private ReturnTypeInferer $returnTypeInferer
    ) {
    }

    /**
     * Checks that in:
     * $this->someCall();
     *
     * The method is fluent class method === returns self
     * public function someClassMethod()
     * {
     *      return $this;
     * }
     */
    public function isFluentClassMethodOfMethodCall(MethodCall $methodCall): bool
    {
        if ($this->isCall($methodCall->var)) {
            return false;
        }

        $calleeStaticType = $this->nodeTypeResolver->getStaticType($methodCall->var);

        // we're not sure
        if ($calleeStaticType instanceof MixedType) {
            return false;
        }

        $methodReturnStaticType = $this->nodeTypeResolver->getStaticType($methodCall);

        // is fluent type
        if (! $calleeStaticType->equals($methodReturnStaticType)) {
            return false;
        }

        if ($calleeStaticType instanceof ObjectType) {
            foreach (self::KNOWN_FACTORY_FLUENT_TYPES as $knownFactoryFluentType) {
                if ($calleeStaticType->isInstanceOf($knownFactoryFluentType)->yes()) {
                    return false;
                }
            }
        }

        return ! $this->isMethodCallCreatingNewInstance($methodCall);
    }

    public function isLastChainMethodCall(MethodCall $methodCall): bool
    {
        // is chain method call
        if (! $methodCall->var instanceof MethodCall && ! $methodCall->var instanceof New_) {
            return false;
        }

        $nextNode = $methodCall->getAttribute(AttributeKey::NEXT_NODE);

        // is last chain call
        return ! $nextNode instanceof Node;
    }

    /**
     * @return string[]|null[]
     */
    public function collectMethodCallNamesInChain(MethodCall $desiredMethodCall): array
    {
        $methodCalls = $this->collectAllMethodCallsInChain($desiredMethodCall);

        $methodNames = [];
        foreach ($methodCalls as $methodCall) {
            $methodNames[] = $this->nodeNameResolver->getName($methodCall->name);
        }

        return $methodNames;
    }

    /**
     * @return MethodCall[]
     */
    public function collectAllMethodCallsInChain(MethodCall $methodCall): array
    {
        $chainMethodCalls = [$methodCall];

        // traverse up
        $currentNode = $methodCall->var;
        while ($currentNode instanceof MethodCall) {
            $chainMethodCalls[] = $currentNode;
            $currentNode = $currentNode->var;
        }

        // traverse down
        if (count($chainMethodCalls) === 1) {
            $currentNode = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
            while ($currentNode instanceof MethodCall) {
                $chainMethodCalls[] = $currentNode;
                $currentNode = $currentNode->getAttribute(AttributeKey::PARENT_NODE);
            }
        }

        return $chainMethodCalls;
    }

    /**
     * @return MethodCall[]
     */
    public function collectAllMethodCallsInChainWithoutRootOne(MethodCall $methodCall): array
    {
        $chainMethodCalls = $this->collectAllMethodCallsInChain($methodCall);

        foreach ($chainMethodCalls as $key => $chainMethodCall) {
            if (! $chainMethodCall->var instanceof MethodCall && ! $chainMethodCall->var instanceof New_) {
                unset($chainMethodCalls[$key]);
                break;
            }
        }

        return array_values($chainMethodCalls);
    }

    /**
     * Checks "$this->someMethod()->anotherMethod()"
     *
     * @param string[] $methods
     */
    public function isTypeAndChainCalls(Node $node, Type $type, array $methods): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        // node chaining is in reverse order than code
        $methods = array_reverse($methods);

        foreach ($methods as $method) {
            if (! $this->nodeNameResolver->isName($node->name, $method)) {
                return false;
            }

            $node = $node->var;
        }

        $variableType = $this->nodeTypeResolver->resolve($node);
        if ($variableType instanceof MixedType) {
            return false;
        }

        return $variableType->isSuperTypeOf($type)
            ->yes();
    }

    public function resolveRootExpr(MethodCall $methodCall): Expr | Name
    {
        $callerNode = $methodCall->var;

        while ($callerNode instanceof MethodCall || $callerNode instanceof StaticCall) {
            $callerNode = $callerNode instanceof StaticCall ? $callerNode->class : $callerNode->var;
        }

        return $callerNode;
    }

    public function resolveRootMethodCall(MethodCall $methodCall): ?MethodCall
    {
        $callerNode = $methodCall->var;

        while ($callerNode instanceof MethodCall && $callerNode->var instanceof MethodCall) {
            $callerNode = $callerNode->var;
        }

        if ($callerNode instanceof MethodCall) {
            return $callerNode;
        }

        return null;
    }

    public function isMethodCallReturnThis(MethodCall $methodCall): bool
    {
        $classMethod = $this->astResolver->resolveClassMethodFromMethodCall($methodCall);
        if (! $classMethod instanceof ClassMethod) {
            return false;
        }

        $inferFunctionLike = $this->returnTypeInferer->inferFunctionLike($classMethod);
        return $inferFunctionLike instanceof ThisType;
    }

    private function isCall(Expr $expr): bool
    {
        if ($expr instanceof MethodCall) {
            return true;
        }

        return $expr instanceof StaticCall;
    }

    private function isMethodCallCreatingNewInstance(MethodCall $methodCall): bool
    {
        $classMethod = $this->astResolver->resolveClassMethodFromMethodCall($methodCall);
        if (! $classMethod instanceof ClassMethod) {
            return false;
        }

        /** @var Return_[] $returns */
        $returns = $this->nodeFinder->findInstanceOf($classMethod, Return_::class);

        foreach ($returns as $return) {
            $expr = $return->expr;

            if (! $expr instanceof Expr) {
                continue;
            }

            if (! $this->isNewInstance($expr)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function isNewInstance(Expr $expr): bool
    {
        if ($expr instanceof Clone_ || $expr instanceof New_) {
            return true;
        }

        return (bool) $this->betterNodeFinder->findFirstPreviousOfNode($expr, function (Node $node) use ($expr): bool {
            if (! $node instanceof Assign) {
                return false;
            }

            if (! $this->nodeComparator->areNodesEqual($node->var, $expr)) {
                return false;
            }

            return $node->expr instanceof Clone_ || $node->expr instanceof New_;
        });
    }
}
