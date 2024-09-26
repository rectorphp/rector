<?php

declare (strict_types=1);
namespace Rector\NodeCollector\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Scalar\MagicConst\Class_;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\Enum\ObjectReference;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeCollector\ValueObject\ArrayCallableDynamicMethod;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\MethodName;
final class ArrayCallableMethodMatcher
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver, ValueResolver $valueResolver, ReflectionProvider $reflectionProvider, ReflectionResolver $reflectionResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->valueResolver = $valueResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * Matches array like: "[$this, 'methodName']" → ['ClassName', 'methodName']
     * Returns ArrayCallableDynamicMethod object when unknown method of callable used, eg: [$this, $other]
     * @see https://github.com/rectorphp/rector-src/pull/908
     * @see https://github.com/rectorphp/rector-src/pull/909
     * @return null|\Rector\NodeCollector\ValueObject\ArrayCallableDynamicMethod|\Rector\NodeCollector\ValueObject\ArrayCallable
     */
    public function match(Array_ $array, Scope $scope, ?string $classMethodName = null)
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
        $callerType = $this->resolveCallerType($firstItemValue, $scope, $classMethodName);
        if (!$callerType instanceof TypeWithClassName) {
            return null;
        }
        if ($array->getAttribute(AttributeKey::IS_ARRAY_IN_ATTRIBUTE) === \true) {
            return null;
        }
        $values = $this->valueResolver->getValue($array);
        $className = $callerType->getClassName();
        $secondItemValue = $items[1]->value;
        if ($values === null) {
            return new ArrayCallableDynamicMethod();
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
        if (!$array->items[0] instanceof ArrayItem) {
            return \true;
        }
        return !$array->items[1] instanceof ArrayItem;
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
        $fromFuncCallName = $array->getAttribute(AttributeKey::FROM_FUNC_CALL_NAME);
        if ($fromFuncCallName === null) {
            return \false;
        }
        return \in_array($fromFuncCallName, $functionNames, \true);
    }
    /**
     * @param \PhpParser\Node\Expr\ClassConstFetch|\PhpParser\Node\Scalar\MagicConst\Class_ $classContext
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\ObjectType
     */
    private function resolveClassContextType($classContext, Scope $scope, ?string $classMethodName)
    {
        $classConstantReference = $this->valueResolver->getValue($classContext);
        // non-class value
        if (!\is_string($classConstantReference)) {
            return new MixedType();
        }
        if ($this->isRequiredClassReflectionResolution($classConstantReference)) {
            $classReflection = $this->reflectionResolver->resolveClassReflection($classContext);
            if (!$classReflection instanceof ClassReflection || !$classReflection->isClass()) {
                return new MixedType();
            }
            $classConstantReference = $classReflection->getName();
        }
        if (!$this->reflectionProvider->hasClass($classConstantReference)) {
            return new MixedType();
        }
        $classReflection = $this->reflectionProvider->getClass($classConstantReference);
        $hasConstruct = $classReflection->hasMethod(MethodName::CONSTRUCT);
        if (!$hasConstruct) {
            return new ObjectType($classConstantReference, null, $classReflection);
        }
        if (\is_string($classMethodName) && $classReflection->hasNativeMethod($classMethodName)) {
            return new ObjectType($classConstantReference, null, $classReflection);
        }
        $extendedMethodReflection = $classReflection->getMethod(MethodName::CONSTRUCT, $scope);
        $parametersAcceptorWithPhpDocs = ParametersAcceptorSelector::combineAcceptors($extendedMethodReflection->getVariants());
        foreach ($parametersAcceptorWithPhpDocs->getParameters() as $parameterReflectionWithPhpDoc) {
            if (!$parameterReflectionWithPhpDoc->getDefaultValue() instanceof Type) {
                return new MixedType();
            }
        }
        return new ObjectType($classConstantReference, null, $classReflection);
    }
    private function resolveCallerType(Expr $expr, Scope $scope, ?string $classMethodName) : Type
    {
        if ($expr instanceof ClassConstFetch || $expr instanceof Class_) {
            // class context means self|static ::class or __CLASS__
            $callerType = $this->resolveClassContextType($expr, $scope, $classMethodName);
        } else {
            $callerType = $this->nodeTypeResolver->getType($expr);
        }
        if ($callerType instanceof ThisType) {
            return $callerType->getStaticObjectType();
        }
        return $callerType;
    }
    private function isRequiredClassReflectionResolution(string $classConstantReference) : bool
    {
        if ($classConstantReference === ObjectReference::STATIC) {
            return \true;
        }
        return $classConstantReference === '__CLASS__';
    }
}
