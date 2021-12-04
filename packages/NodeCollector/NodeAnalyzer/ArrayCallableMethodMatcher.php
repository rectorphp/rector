<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeCollector\ValueObject\ArrayCallableDynamicMethod;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class ArrayCallableMethodMatcher
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly NodeTypeResolver $nodeTypeResolver,
        private readonly ValueResolver $valueResolver,
        private readonly ReflectionProvider $reflectionProvider,
        private readonly BetterNodeFinder $betterNodeFinder,
    ) {
    }

    /**
     * Matches array like: "[$this, 'methodName']" → ['ClassName', 'methodName']
     * Returns ArrayCallableDynamicMethod object when unknown method of callable used, eg: [$this, $other]
     * @see https://github.com/rectorphp/rector-src/pull/908
     * @see https://github.com/rectorphp/rector-src/pull/909
     */
    public function match(Array_ $array): null | ArrayCallableDynamicMethod | ArrayCallable
    {
        $arrayItems = $array->items;
        if (count($arrayItems) !== 2) {
            return null;
        }

        if ($this->shouldSkipNullItems($array)) {
            return null;
        }

        /** @var ArrayItem[] $items */
        $items = $array->items;

        // $this, self, static, FQN
        $firstItemValue = $items[0]->value;

        $calleeType = $firstItemValue instanceof ClassConstFetch
            // static ::class reference?
            ? $this->resolveClassConstFetchType($firstItemValue)
            : $this->nodeTypeResolver->getType($firstItemValue);

        if (! $calleeType instanceof TypeWithClassName) {
            return null;
        }

        $values = $this->valueResolver->getValue($array);
        $className = $calleeType->getClassName();
        $secondItemValue = $items[1]->value;

        if ($values === null) {
            return new ArrayCallableDynamicMethod($firstItemValue, $className, $secondItemValue);
        }

        if ($this->shouldSkipAssociativeArray($values)) {
            return null;
        }

        if (! $secondItemValue instanceof String_) {
            return null;
        }

        if ($this->isCallbackAtFunctionNames($array, ['register_shutdown_function', 'forward_static_call'])) {
            return null;
        }

        $methodName = $secondItemValue->value;
        if ($methodName === MethodName::CONSTRUCT) {
            return null;
        }

        return new ArrayCallable($firstItemValue, $className, $methodName);
    }

    private function shouldSkipNullItems(Array_ $array): bool
    {
        if ($array->items[0] === null) {
            return true;
        }

        return $array->items[1] === null;
    }

    /**
     * @param mixed $values
     */
    private function shouldSkipAssociativeArray($values): bool
    {
        if (! is_array($values)) {
            return false;
        }

        $keys = array_keys($values);
        return $keys !== [0, 1] && $keys !== [1];
    }

    /**
     * @param string[] $functionNames
     */
    private function isCallbackAtFunctionNames(Array_ $array, array $functionNames): bool
    {
        $parentNode = $array->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Arg) {
            return false;
        }

        $parentParentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentParentNode instanceof FuncCall) {
            return false;
        }

        return $this->nodeNameResolver->isNames($parentParentNode, $functionNames);
    }

    private function resolveClassConstFetchType(ClassConstFetch $classConstFetch): MixedType | ObjectType
    {
        $classConstantReference = $this->valueResolver->getValue($classConstFetch);

        if (ObjectReference::STATIC()->getValue() === $classConstantReference) {
            $classLike = $this->betterNodeFinder->findParentType($classConstFetch, Class_::class);
            if (! $classLike instanceof ClassLike) {
                return new MixedType();
            }

            $classConstantReference = (string) $this->nodeNameResolver->getName($classLike);
        }

        // non-class value
        if (! is_string($classConstantReference)) {
            return new MixedType();
        }

        if (! $this->reflectionProvider->hasClass($classConstantReference)) {
            return new MixedType();
        }

        $scope = $classConstFetch->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return new MixedType();
        }

        $classReflection = $this->reflectionProvider->getClass($classConstantReference);
        $hasConstruct = $classReflection->hasMethod(MethodName::CONSTRUCT);

        if (! $hasConstruct) {
            return new ObjectType($classConstantReference, null, $classReflection);
        }

        $methodReflection = $classReflection->getMethod(MethodName::CONSTRUCT, $scope);
        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());

        foreach ($parametersAcceptor->getParameters() as $parameterReflection) {
            if ($parameterReflection->getDefaultValue() === null) {
                return new MixedType();
            }
        }

        return new ObjectType($classConstantReference, null, $classReflection);
    }
}
