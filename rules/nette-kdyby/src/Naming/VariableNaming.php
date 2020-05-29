<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Naming;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\Exception\NotImplementedException;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeNameResolver\NodeNameResolver;

final class VariableNaming
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        ValueResolver $valueResolver,
        ClassNaming $classNaming
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
        $this->classNaming = $classNaming;
    }

    public function resolveFromNode(Node $node, Type $type): string
    {
        $variableName = $this->resolveBareFromNode($node);

        // adjust static to specific class
        if ($variableName === 'this' && $type instanceof ThisType) {
            $shortClassName = $this->classNaming->getShortName($type->getClassName());
            $variableName = lcfirst($shortClassName);
        }

        return StaticRectorStrings::underscoreToPascalCase($variableName);
    }

    private function resolveFromPropertyFetch(PropertyFetch $propertyFetch): string
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

    private function resolveFromMethodCall(MethodCall $methodCall): string
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

    private function resolveParamNameFromArrayDimFetch(ArrayDimFetch $arrayDimFetch): string
    {
        while ($arrayDimFetch instanceof ArrayDimFetch) {
            if ($arrayDimFetch->dim instanceof Scalar) {
                $valueName = $this->nodeNameResolver->getName($arrayDimFetch->var);
                $dimName = $this->valueResolver->getValue($arrayDimFetch->dim);

                $dimName = StaticRectorStrings::underscoreToCamelCase($dimName);

                return $valueName . $dimName;
            }

            $arrayDimFetch = $arrayDimFetch->var;
        }

        return $this->resolveBareFromNode($arrayDimFetch);
    }

    private function resolveBareFromNode(Node $node): string
    {
        if ($node instanceof Arg) {
            $node = $node->value;
        }

        if ($node instanceof Cast) {
            $node = $node->expr;
        }

        if ($node instanceof Ternary) {
            $node = $node->if;
        }

        if ($node instanceof ArrayDimFetch) {
            return $this->resolveParamNameFromArrayDimFetch($node);
        }

        if ($node instanceof PropertyFetch) {
            return $this->resolveFromPropertyFetch($node);
        }

        if ($node instanceof MethodCall) {
            return $this->resolveFromMethodCall($node);
        }

        if ($node === null) {
            throw new NotImplementedException();
        }

        $paramName = $this->nodeNameResolver->getName($node);
        if ($paramName !== null) {
            return $paramName;
        }

        if ($node instanceof String_) {
            return $node->value;
        }

        throw new NotImplementedException();
    }
}
