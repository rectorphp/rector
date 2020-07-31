<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\MagicDisclosure\ValueObject\AssignAndRootExpr;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;

final class ChainMethodCallNodeAnalyzer
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var TypeUnwrapper
     */
    private $typeUnwrapper;

    public function __construct(NodeTypeResolver $nodeTypeResolver, TypeUnwrapper $typeUnwrapper)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeUnwrapper = $typeUnwrapper;
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
        return $calleeStaticType->equals($methodReturnStaticType);
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
        $rootClassType = $this->resolveExprStringClassType($assignAndRootExpr->getRootExpr());
        if ($rootClassType === null) {
            return [];
        }

        $callerClassTypes = [];
        $callerClassTypes[] = $rootClassType;

        foreach ($chainMethodCalls as $chainMethodCall) {
            $chainMethodCallType = $this->resolveExprStringClassType($chainMethodCall);
            if ($chainMethodCallType === null) {
                return [];
            }

            $callerClassTypes[] = $chainMethodCallType;
        }

        return array_unique($callerClassTypes);
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

    private function resolveExprStringClassType(Expr $expr): ?string
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
}
