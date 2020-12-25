<?php

declare(strict_types=1);

namespace Rector\Defluent\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PHPStan\Type\MixedType;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class NewFluentChainMethodCallNodeAnalyzer
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function isNewMethodCallReturningSelf(MethodCall $methodCall): bool
    {
        $newStaticType = $this->nodeTypeResolver->getStaticType($methodCall->var);
        $methodCallStaticType = $this->nodeTypeResolver->getStaticType($methodCall);

        return $methodCallStaticType->equals($newStaticType);
    }

    /**
     * Method call with "new X", that returns "X"?
     * e.g.
     *
     * $this->setItem(new Item) // → returns "Item"
     */
    public function matchNewInFluentSetterMethodCall(MethodCall $methodCall): ?New_
    {
        if (count($methodCall->args) !== 1) {
            return null;
        }

        $onlyArgValue = $methodCall->args[0]->value;
        if (! $onlyArgValue instanceof New_) {
            return null;
        }

        $newType = $this->nodeTypeResolver->resolve($onlyArgValue);
        if ($newType instanceof MixedType) {
            return null;
        }

        $parentMethodCallReturnType = $this->nodeTypeResolver->resolve($methodCall);
        if (! $newType->equals($parentMethodCallReturnType)) {
            return null;
        }

        return $onlyArgValue;
    }
}
