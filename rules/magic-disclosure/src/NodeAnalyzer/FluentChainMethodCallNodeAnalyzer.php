<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\MagicDisclosure\ValueObject\AssignAndRootExpr;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;

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
     * @var string[]
     */
    private const KNOWN_FACTORY_FLUENT_TYPES = ['PHPStan\Analyser\MutatingScope'];

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var TypeUnwrapper
     */
    private $typeUnwrapper;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver,
        TypeUnwrapper $typeUnwrapper
    ) {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeUnwrapper = $typeUnwrapper;
        $this->nodeNameResolver = $nodeNameResolver;
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
        if ($methodCall->var instanceof MethodCall || $methodCall->var instanceof StaticCall) {
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

        if ($calleeStaticType instanceof TypeWithClassName) {
            foreach (self::KNOWN_FACTORY_FLUENT_TYPES as $knownFactoryFluentTypes) {
                if (is_a($calleeStaticType->getClassName(), $knownFactoryFluentTypes, true)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function isLastChainMethodCall(MethodCall $methodCall): bool
    {
        // is chain method call
        if (! $methodCall->var instanceof MethodCall && ! $methodCall->var instanceof New_) {
            return false;
        }

        $nextNode = $methodCall->getAttribute(AttributeKey::NEXT_NODE);

        // is last chain call
        return $nextNode === null;
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     * @return string[]
     */
    public function resolveCalleeUniqueTypes(AssignAndRootExpr $assignAndRootExpr, array $chainMethodCalls): array
    {
        $rootClassType = $this->resolveStringTypeFromExpr($assignAndRootExpr->getRootExpr());
        if ($rootClassType === null) {
            return [];
        }

        $callerClassTypes = [];
        $callerClassTypes[] = $rootClassType;

        // chain method calls are inversed
        $lastChainMethodCallKey = array_key_first($chainMethodCalls);

        foreach ($chainMethodCalls as $key => $chainMethodCall) {
            $chainMethodCallType = $this->resolveStringTypeFromExpr($chainMethodCall);

            if ($chainMethodCallType === null) {
                // last method call does not need a type
                if ($lastChainMethodCallKey === $key) {
                    continue;
                }

                return [];
            }

            $callerClassTypes[] = $chainMethodCallType;
        }

        $uniqueCallerClassTypes = array_unique($callerClassTypes);

        return $this->filterOutAlreadyPresentParentClasses($uniqueCallerClassTypes);
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
            $activeMethodName = $this->nodeNameResolver->getName($node->name);
            if ($activeMethodName !== $method) {
                return false;
            }

            $node = $node->var;
            if ($node instanceof MethodCall) {
                continue;
            }
        }

        $variableType = $this->nodeTypeResolver->resolve($node);
        if ($variableType instanceof MixedType) {
            return false;
        }

        return $variableType->isSuperTypeOf($type)->yes();
    }

    public function resolveRootVariable(MethodCall $methodCall): Node
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

    private function resolveStringTypeFromExpr(Expr $expr): ?string
    {
        $rootStaticType = $this->nodeTypeResolver->getStaticType($expr);
        if ($rootStaticType instanceof UnionType) {
            $rootStaticType = $this->typeUnwrapper->unwrapNullableType($rootStaticType);
        }

        if (! $rootStaticType instanceof TypeWithClassName) {
            // nothing we can do, unless
            return null;
        }

        return $rootStaticType->getClassName();
    }

    /**
     * If a child class is with the parent class in the list, count them as 1
     *
     * @param string[] $types
     * @return string[]
     */
    private function filterOutAlreadyPresentParentClasses(array $types): array
    {
        $secondTypes = $types;

        foreach ($types as $key => $type) {
            foreach ($secondTypes as $secondType) {
                if ($type === $secondType) {
                    continue;
                }

                if (is_a($type, $secondType, true)) {
                    unset($types[$key]);
                    continue 2;
                }
            }
        }

        return array_values($types);
    }
}
