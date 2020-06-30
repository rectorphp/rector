<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use Rector\Core\ValueObject\AssignAndRootExpr;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class ChainMethodCallNodeAnalyzer
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
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
     */
    public function isCalleeSingleType(AssignAndRootExpr $assignAndRootExpr, array $chainMethodCalls): bool
    {
        $rootStaticType = $this->nodeTypeResolver->getStaticType($assignAndRootExpr->getRootExpr());

        foreach ($chainMethodCalls as $chainMethodCall) {
            $chainMethodCallStaticType = $this->nodeTypeResolver->getStaticType($chainMethodCall);
            if (! $chainMethodCallStaticType->equals($rootStaticType)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return MethodCall[]
     */
    public function collectAllMethodCallsInChain(MethodCall $methodCall): array
    {
        $chainMethodCalls = [$methodCall];

        $currentNode = $methodCall->var;
        while ($currentNode instanceof MethodCall) {
            $chainMethodCalls[] = $currentNode;
            $currentNode = $currentNode->var;
        }

        return $chainMethodCalls;
    }
}
