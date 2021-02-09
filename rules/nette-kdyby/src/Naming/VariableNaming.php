<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Naming;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\Util\StaticInstanceOf;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * @todo decouple to collector?
 */
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
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        ValueResolver $valueResolver,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function resolveFromNode(Node $node): ?string
    {
        $nodeType = $this->nodeTypeResolver->getStaticType($node);
        return $this->resolveFromNodeAndType($node, $nodeType);
    }

    public function resolveFromNodeAndType(Node $node, Type $type): ?string
    {
        $variableName = $this->resolveBareFromNode($node);
        if ($variableName === null) {
            return null;
        }

        // adjust static to specific class
        if ($variableName === 'this' && $type instanceof ThisType) {
            $shortClassName = $this->nodeNameResolver->getShortName($type->getClassName());
            $variableName = lcfirst($shortClassName);
        }

        return StaticRectorStrings::underscoreToCamelCase($variableName);
    }

    public function resolveFromNodeWithScopeCountAndFallbackName(
        Expr $expr,
        Scope $scope,
        string $fallbackName
    ): string {
        $name = $this->resolveFromNode($expr);
        if ($name === null) {
            $name = $fallbackName;
        }

        if (Strings::contains($name, '\\')) {
            $name = (string) Strings::after($name, '\\', - 1);
        }

        $countedValueName = $this->createCountedValueName($name, $scope);
        return lcfirst($countedValueName);
    }

    public function createCountedValueName(string $valueName, ?Scope $scope): string
    {
        if ($scope === null) {
            return $valueName;
        }

        // make sure variable name is unique
        if (! $scope->hasVariableType($valueName)->yes()) {
            return $valueName;
        }

        // we need to add number suffix until the variable is unique
        $i = 2;
        $countedValueNamePart = $valueName;
        while ($scope->hasVariableType($valueName)->yes()) {
            $valueName = $countedValueNamePart . $i;
            ++$i;
        }

        return $valueName;
    }

    public function resolveFromFuncCallFirstArgumentWithSuffix(
        FuncCall $funcCall,
        string $suffix,
        string $fallbackName,
        ?Scope $scope
    ): string {
        $bareName = $this->resolveBareFuncCallArgumentName($funcCall, $fallbackName, $suffix);
        return $this->createCountedValueName($bareName, $scope);
    }

    private function resolveBareFromNode(Node $node): ?string
    {
        $node = $this->unwrapNode($node);

        if ($node instanceof ArrayDimFetch) {
            return $this->resolveParamNameFromArrayDimFetch($node);
        }

        if ($node instanceof PropertyFetch) {
            return $this->resolveFromPropertyFetch($node);
        }

        if ($this->isCall($node)) {
            return $this->resolveFromMethodCall($node);
        }

        if ($node instanceof New_) {
            return $this->resolveFromNew($node);
        }

        if ($node instanceof FuncCall) {
            return $this->resolveFromNode($node->name);
        }

        if (! $node instanceof Node) {
            throw new NotImplementedYetException();
        }

        $paramName = $this->nodeNameResolver->getName($node);
        if ($paramName !== null) {
            return $paramName;
        }

        if ($node instanceof String_) {
            return $node->value;
        }

        return null;
    }

    private function resolveBareFuncCallArgumentName(FuncCall $funcCall, string $fallbackName, string $suffix): string
    {
        $argumentValue = $funcCall->args[0]->value;
        if ($argumentValue instanceof MethodCall || $argumentValue instanceof StaticCall) {
            $name = $this->nodeNameResolver->getName($argumentValue->name);
        } else {
            $name = $this->nodeNameResolver->getName($argumentValue);
        }

        if ($name === null) {
            return $fallbackName;
        }

        return $name . $suffix;
    }

    private function unwrapNode(Node $node): ?Node
    {
        if ($node instanceof Arg) {
            return $node->value;
        }

        if ($node instanceof Cast) {
            return $node->expr;
        }

        if ($node instanceof Ternary) {
            return $node->if;
        }

        return $node;
    }

    private function resolveParamNameFromArrayDimFetch(ArrayDimFetch $arrayDimFetch): ?string
    {
        while ($arrayDimFetch instanceof ArrayDimFetch) {
            if ($arrayDimFetch->dim instanceof Scalar) {
                $valueName = $this->nodeNameResolver->getName($arrayDimFetch->var);
                $dimName = $this->valueResolver->getValue($arrayDimFetch->dim);

                $dimName = StaticRectorStrings::underscoreToPascalCase($dimName);

                return $valueName . $dimName;
            }

            $arrayDimFetch = $arrayDimFetch->var;
        }

        return $this->resolveBareFromNode($arrayDimFetch);
    }

    private function resolveFromPropertyFetch(PropertyFetch $propertyFetch): string
    {
        $varName = $this->nodeNameResolver->getName($propertyFetch->var);
        if (! is_string($varName)) {
            throw new NotImplementedYetException();
        }

        $propertyName = $this->nodeNameResolver->getName($propertyFetch->name);
        if (! is_string($propertyName)) {
            throw new NotImplementedYetException();
        }

        if ($varName === 'this') {
            return $propertyName;
        }

        return $varName . ucfirst($propertyName);
    }

    private function isCall(?Node $node): bool
    {
        if (! $node instanceof Node) {
            return false;
        }

        return StaticInstanceOf::isOneOf($node, [MethodCall::class, NullsafeMethodCall::class, StaticCall::class]);
    }

    private function resolveFromMethodCall(?Node $node): ?string
    {
        if (! $node instanceof Node) {
            return null;
        }

        if (! property_exists($node, 'name')) {
            return null;
        }

        if ($node->name instanceof MethodCall) {
            return $this->resolveFromMethodCall($node->name);
        }

        $methodName = $this->nodeNameResolver->getName($node->name);
        if (! is_string($methodName)) {
            return null;
        }

        return $methodName;
    }

    private function resolveFromNew(New_ $new): string
    {
        if ($new->class instanceof Name) {
            $className = $this->nodeNameResolver->getName($new->class);
            return $this->nodeNameResolver->getShortName($className);
        }

        throw new NotImplementedYetException();
    }
}
