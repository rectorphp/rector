<?php

declare(strict_types=1);

namespace Rector\PHPStan\Reflection;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\FunctionNotFoundException;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantArrayTypeAndMethod;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class CallReflectionResolver
{
    /**
     * Took from https://github.com/phpstan/phpstan-src/blob/8376548f76e2c845ae047e3010e873015b796818/src/Type/Constant/ConstantStringType.php#L158
     *
     * @see https://regex101.com/r/IE6lcM/4
     *
     * @var string
     */
    private const STATIC_METHOD_REGEXP = '#^([a-zA-Z_\\x7f-\\xff\\\\][a-zA-Z0-9_\\x7f-\\xff\\\\]*)::([a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*)\\z#';

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(
        ReflectionProvider $reflectionProvider,
        NodeTypeResolver $nodeTypeResolver,
        NameResolver $nameResolver
    ) {
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nameResolver = $nameResolver;
    }

    /**
     * @param FuncCall|MethodCall|StaticCall $node
     * @return FunctionReflection|MethodReflection|null
     */
    public function resolveCall(Node $node)
    {
        if ($node instanceof FuncCall) {
            return $this->resolveFunctionCall($node);
        }

        return $this->resolveMethodCall($node);
    }

    /**
     * @return FunctionReflection|MethodReflection|null
     */
    public function resolveFunctionCall(FuncCall $funcCall)
    {
        /** @var Scope|null $scope */
        $scope = $funcCall->getAttribute(AttributeKey::SCOPE);

        if ($funcCall->name instanceof Name) {
            try {
                return $this->reflectionProvider->getFunction($funcCall->name, $scope);
            } catch (FunctionNotFoundException $functionNotFoundException) {
                return null;
            }
        }

        if ($scope === null) {
            return null;
        }

        $type = $scope->getType($funcCall->name);

        if ($type instanceof ObjectType) {
            return $this->resolveInvokable($type);
        }

        if ($type instanceof ConstantStringType) {
            return $this->resolveConstantString($type, $scope);
        }

        if ($type instanceof ConstantArrayType) {
            return $this->resolveConstantArray($type, $scope);
        }

        return new NativeFunctionReflection(
            '{closure}',
            $type->getCallableParametersAcceptors($scope),
            null,
            TrinaryLogic::createMaybe()
        );
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    public function resolveMethodCall(Node $node): ?MethodReflection
    {
        /** @var Scope|null $scope */
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if ($scope === null) {
            return null;
        }

        $classType = $this->nodeTypeResolver->getObjectType(
            $node instanceof MethodCall ? $node->var : $node->class
        );
        $methodName = $this->nameResolver->getName($node->name);

        if ($methodName === null || ! $classType->hasMethod($methodName)->yes()) {
            return null;
        }

        return $classType->getMethod($methodName, $scope);
    }

    /**
     * @param FunctionReflection|MethodReflection|null $reflection
     * @param FuncCall|MethodCall|StaticCall $node
     */
    public function resolveParametersAcceptor($reflection, Node $node): ?ParametersAcceptor
    {
        if ($reflection === null) {
            return null;
        }

        $variants = $reflection->getVariants();
        $nbVariants = count($variants);

        if ($nbVariants === 0) {
            return null;
        } elseif ($nbVariants === 1) {
            $parametersAcceptor = ParametersAcceptorSelector::selectSingle($variants);
        } else {
            /** @var Scope|null $scope */
            $scope = $node->getAttribute(AttributeKey::SCOPE);
            if ($scope === null) {
                return null;
            }

            $parametersAcceptor = ParametersAcceptorSelector::selectFromArgs($scope, $node->args, $variants);
        }

        return $parametersAcceptor;
    }

    /**
     * @see https://github.com/phpstan/phpstan-src/blob/b1fd47bda2a7a7d25091197b125c0adf82af6757/src/Type/ObjectType.php#L705
     */
    private function resolveInvokable(ObjectType $objectType): ?MethodReflection
    {
        $className = $objectType->getClassName();
        if (! $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        if (! $classReflection->hasNativeMethod('__invoke')) {
            return null;
        }

        return $classReflection->getNativeMethod('__invoke');
    }

    /**
     * @see https://github.com/phpstan/phpstan-src/blob/b1fd47bda2a7a7d25091197b125c0adf82af6757/src/Type/Constant/ConstantStringType.php#L147
     *
     * @return FunctionReflection|MethodReflection|null
     */
    private function resolveConstantString(ConstantStringType $constantStringType, Scope $scope)
    {
        $value = $constantStringType->getValue();

        // 'my_function'
        $functionName = new Name($value);
        if ($this->reflectionProvider->hasFunction($functionName, null)) {
            return $this->reflectionProvider->getFunction($functionName, null);
        }

        // 'MyClass::myStaticFunction'
        $matches = Strings::match($value, self::STATIC_METHOD_REGEXP);
        if ($matches === null) {
            return null;
        }

        if (! $this->reflectionProvider->hasClass($matches[1])) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($matches[1]);
        if (! $classReflection->hasMethod($matches[2])) {
            return null;
        }

        return $classReflection->getMethod($matches[2], $scope);
    }

    /**
     * @see https://github.com/phpstan/phpstan-src/blob/b1fd47bda2a7a7d25091197b125c0adf82af6757/src/Type/Constant/ConstantArrayType.php#L188
     */
    private function resolveConstantArray(ConstantArrayType $constantArrayType, Scope $scope): ?MethodReflection
    {
        $typeAndMethodName = $this->findTypeAndMethodName($constantArrayType);
        if ($typeAndMethodName === null) {
            return null;
        }

        if ($typeAndMethodName->isUnknown() || ! $typeAndMethodName->getCertainty()->yes()) {
            return null;
        }

        $method = $typeAndMethodName
            ->getType()
            ->getMethod($typeAndMethodName->getMethod(), $scope);

        if (! $scope->canCallMethod($method)) {
            return null;
        }

        return $method;
    }

    /**
     * @see https://github.com/phpstan/phpstan-src/blob/b1fd47bda2a7a7d25091197b125c0adf82af6757/src/Type/Constant/ConstantArrayType.php#L209
     */
    private function findTypeAndMethodName(ConstantArrayType $constantArrayType): ?ConstantArrayTypeAndMethod
    {
        if (! $this->areKeyTypesValid($constantArrayType)) {
            return null;
        }

        [$classOrObject, $method] = $constantArrayType->getValueTypes();

        if (! $method instanceof ConstantStringType) {
            return ConstantArrayTypeAndMethod::createUnknown();
        }

        if ($classOrObject instanceof ConstantStringType) {
            if (! $this->reflectionProvider->hasClass($classOrObject->getValue())) {
                return ConstantArrayTypeAndMethod::createUnknown();
            }

            $type = new ObjectType($this->reflectionProvider->getClass($classOrObject->getValue())->getName());
        } elseif ((new ObjectWithoutClassType())->isSuperTypeOf($classOrObject)->yes()) {
            $type = $classOrObject;
        } else {
            return ConstantArrayTypeAndMethod::createUnknown();
        }

        $has = $type->hasMethod($method->getValue());
        if (! $has->no()) {
            return ConstantArrayTypeAndMethod::createConcrete($type, $method->getValue(), $has);
        }

        return null;
    }

    private function areKeyTypesValid(ConstantArrayType $constantArrayType): bool
    {
        $keyTypes = $constantArrayType->getKeyTypes();

        if (count($keyTypes) !== 2) {
            return false;
        }

        if ($keyTypes[0]->isSuperTypeOf(new ConstantIntegerType(0))->no()) {
            return false;
        }

        if ($keyTypes[1]->isSuperTypeOf(new ConstantIntegerType(1))->no()) {
            return false;
        }

        return true;
    }
}
