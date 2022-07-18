<?php

declare (strict_types=1);
namespace Rector\NodeCollector\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr;
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
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
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
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, ValueResolver $valueResolver, ReflectionProvider $reflectionProvider, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->valueResolver = $valueResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * Matches array like: "[$this, 'methodName']" → ['ClassName', 'methodName']
     * Returns ArrayCallableDynamicMethod object when unknown method of callable used, eg: [$this, $other]
     * @see https://github.com/rectorphp/rector-src/pull/908
     * @see https://github.com/rectorphp/rector-src/pull/909
     * @return null|\Rector\NodeCollector\ValueObject\ArrayCallableDynamicMethod|\Rector\NodeCollector\ValueObject\ArrayCallable
     */
    public function match(Array_ $array)
    {
        if (\count($array->items) !== 2) {
            return null;
        }
        if ($this->shouldSkipNullItems($array)) {
            return null;
        }
        /** @var ArrayItem[] $items */
        $items = $array->items;
        // $this, self, static, FQN
        $firstItemValue = $items[0]->value;
        $callerType = $this->resolveCallerType($firstItemValue);
        if (!$callerType instanceof TypeWithClassName) {
            return null;
        }
        $isInAttribute = (bool) $this->betterNodeFinder->findParentType($array, Attribute::class);
        if ($isInAttribute) {
            return null;
        }
        $values = $this->valueResolver->getValue($array);
        $className = $callerType->getClassName();
        $secondItemValue = $items[1]->value;
        if ($values === null) {
            return new ArrayCallableDynamicMethod($firstItemValue, $className, $secondItemValue);
        }
        if ($this->shouldSkipAssociativeArray($values)) {
            return null;
        }
        if (!$secondItemValue instanceof String_) {
            return null;
        }
        if ($this->isCallbackAtFunctionNames($array, ['register_shutdown_function', 'forward_static_call'])) {
            return null;
        }
        $methodName = $secondItemValue->value;
        if ($methodName === MethodName::CONSTRUCT) {
            return null;
        }
        // skip non-existing methods
        if (!$callerType->hasMethod($methodName)->yes()) {
            return null;
        }
        return new ArrayCallable($firstItemValue, $className, $methodName);
    }
    private function shouldSkipNullItems(Array_ $array) : bool
    {
        if ($array->items[0] === null) {
            return \true;
        }
        return $array->items[1] === null;
    }
    /**
     * @param mixed $values
     */
    private function shouldSkipAssociativeArray($values) : bool
    {
        if (!\is_array($values)) {
            return \false;
        }
        $keys = \array_keys($values);
        return $keys !== [0, 1] && $keys !== [1];
    }
    /**
     * @param string[] $functionNames
     */
    private function isCallbackAtFunctionNames(Array_ $array, array $functionNames) : bool
    {
        $parentNode = $array->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Arg) {
            return \false;
        }
        $parentParentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentParentNode instanceof FuncCall) {
            return \false;
        }
        return $this->nodeNameResolver->isNames($parentParentNode, $functionNames);
    }
    /**
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\ObjectType
     */
    private function resolveClassConstFetchType(ClassConstFetch $classConstFetch)
    {
        $classConstantReference = $this->valueResolver->getValue($classConstFetch);
        if ($classConstantReference === ObjectReference::STATIC) {
            $classLike = $this->betterNodeFinder->findParentType($classConstFetch, Class_::class);
            if (!$classLike instanceof ClassLike) {
                return new MixedType();
            }
            $classConstantReference = (string) $this->nodeNameResolver->getName($classLike);
        }
        // non-class value
        if (!\is_string($classConstantReference)) {
            return new MixedType();
        }
        if (!$this->reflectionProvider->hasClass($classConstantReference)) {
            return new MixedType();
        }
        $scope = $classConstFetch->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return new MixedType();
        }
        $classReflection = $this->reflectionProvider->getClass($classConstantReference);
        $hasConstruct = $classReflection->hasMethod(MethodName::CONSTRUCT);
        if (!$hasConstruct) {
            return new ObjectType($classConstantReference, null, $classReflection);
        }
        $extendedMethodReflection = $classReflection->getMethod(MethodName::CONSTRUCT, $scope);
        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($extendedMethodReflection->getVariants());
        foreach ($parametersAcceptor->getParameters() as $parameterReflection) {
            if ($parameterReflection->getDefaultValue() === null) {
                return new MixedType();
            }
        }
        return new ObjectType($classConstantReference, null, $classReflection);
    }
    private function resolveCallerType(Expr $expr) : Type
    {
        if ($expr instanceof ClassConstFetch) {
            // static ::class reference?
            $callerType = $this->resolveClassConstFetchType($expr);
        } else {
            $callerType = $this->nodeTypeResolver->getType($expr);
        }
        if ($callerType instanceof ThisType) {
            return $callerType->getStaticObjectType();
        }
        return $callerType;
    }
}
