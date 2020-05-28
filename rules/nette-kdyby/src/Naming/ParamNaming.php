<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Naming;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Cast\String_ as StringCasting;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Exception\NotImplementedException;
use Rector\NodeNameResolver\NodeNameResolver;

final class ParamNaming
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function resolveParamNameFromArg(Arg $arg): string
    {
        $value = $arg->value;
        if ($value instanceof StringCasting) {
            $value = $value->expr;
        }

        if ($value instanceof Ternary) {
            $value = $value->if;
        }

        while ($value instanceof ArrayDimFetch) {
            $value = $value->var;
        }

        if ($value instanceof PropertyFetch) {
            return $this->resolveParamNameFromPropertyFetch($value);
        }

        if ($value instanceof MethodCall) {
            return $this->resolveParamNameFromMethodCall($value);
        }

        if ($value === null) {
            throw new NotImplementedException();
        }

        $paramName = $this->nodeNameResolver->getName($value);
        if ($paramName !== null) {
            return $paramName;
        }

        if ($value instanceof String_) {
            return $value->value;
        }

        throw new NotImplementedException();
    }

    public function resolveParamNameFromPropertyFetch(PropertyFetch $propertyFetch): string
    {
        $varName = $this->nodeNameResolver->getName($propertyFetch->var);
        if (! is_string($varName)) {
            throw new NotImplementedException();
        }

        $propertyName = $this->nodeNameResolver->getName($propertyFetch->name);
        if (! is_string($propertyName)) {
            throw new NotImplementedException();
        }

        return $varName . ucfirst($propertyName);
    }

    public function resolveParamNameFromMethodCall(MethodCall $methodCall): string
    {
        $varName = $this->nodeNameResolver->getName($methodCall->var);
        if (! is_string($varName)) {
            throw new NotImplementedException();
        }

        $methodName = $this->nodeNameResolver->getName($methodCall->name);
        if (! is_string($methodName)) {
            throw new NotImplementedException();
        }

        return $varName . ucfirst($methodName);
    }
}
