<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Naming;

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
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\Exception\NotImplementedException;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
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
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        ClassNaming $classNaming,
        NodeNameResolver $nodeNameResolver,
        ValueResolver $valueResolver,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
        $this->classNaming = $classNaming;
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
            $shortClassName = $this->classNaming->getShortName($type->getClassName());
            $variableName = lcfirst($shortClassName);
        }

        return StaticRectorStrings::underscoreToPascalCase($variableName);
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

        return lcfirst($this->createCountedValueName($name, $scope));
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

        if ($node instanceof MethodCall
            || $node instanceof NullsafeMethodCall
            || $node instanceof StaticCall
        ) {
            return $this->resolveFromMethodCall($node);
        }

        if ($node instanceof New_) {
            return $this->resolveFromNew($node);
        }

        if ($node instanceof FuncCall) {
            return $this->resolveFromNode($node->name);
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

                $dimName = StaticRectorStrings::underscoreToCamelCase($dimName);

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
            throw new NotImplementedException();
        }

        $propertyName = $this->nodeNameResolver->getName($propertyFetch->name);
        if (! is_string($propertyName)) {
            throw new NotImplementedException();
        }

        if ($varName === 'this') {
            return $propertyName;
        }

        return $varName . ucfirst($propertyName);
    }

    private function resolveFromMethodCall(Expr $expr): ?string
    {
        if (! $expr instanceof MethodCall
            && ! $expr instanceof NullsafeMethodCall
            && ! $expr instanceof StaticCall
        ) {
            $allowedTypes = [MethodCall::class, NullsafeMethodCall::class, StaticCall::class];

            throw new ShouldNotHappenException(sprintf(
                'Only "%s" are supported, "%s" given',
                implode('", "', $allowedTypes),
                get_class($expr)
            ));
        }

        if ($expr->name instanceof MethodCall) {
            return $this->resolveFromMethodCall($expr->name);
        }

        $methodName = $this->nodeNameResolver->getName($expr->name);
        if (! is_string($methodName)) {
            return null;
        }

        return $methodName;
    }

    private function resolveFromNew(New_ $new): string
    {
        if ($new->class instanceof Name) {
            $className = $this->nodeNameResolver->getName($new->class);
            return $this->classNaming->getShortName($className);
        }

        throw new NotImplementedYetException();
    }
}
