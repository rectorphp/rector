<?php

declare (strict_types=1);
namespace Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantArrayTypeAndMethod;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverInterface;
/**
 * @see https://github.com/phpstan/phpstan-src/blob/b1fd47bda2a7a7d25091197b125c0adf82af6757/src/Type/Constant/ConstantArrayType.php#L188
 *
 * @implements TypeToCallReflectionResolverInterface<ConstantArrayType>
 */
final class ConstantArrayTypeToCallReflectionResolver implements \Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverInterface
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function supports(\PHPStan\Type\Type $type) : bool
    {
        return $type instanceof \PHPStan\Type\Constant\ConstantArrayType;
    }
    /**
     * @param ConstantArrayType $type
     */
    public function resolve(\PHPStan\Type\Type $type, \PHPStan\Analyser\Scope $scope) : ?\PHPStan\Reflection\MethodReflection
    {
        $constantArrayTypeAndMethod = $this->findTypeAndMethodName($type);
        if (!$constantArrayTypeAndMethod instanceof \PHPStan\Type\Constant\ConstantArrayTypeAndMethod) {
            return null;
        }
        if ($constantArrayTypeAndMethod->isUnknown()) {
            return null;
        }
        if (!$constantArrayTypeAndMethod->getCertainty()->yes()) {
            return null;
        }
        $constantArrayType = $constantArrayTypeAndMethod->getType();
        $methodReflection = $constantArrayType->getMethod($constantArrayTypeAndMethod->getMethod(), $scope);
        if (!$scope->canCallMethod($methodReflection)) {
            return null;
        }
        return $methodReflection;
    }
    /**
     * @see https://github.com/phpstan/phpstan-src/blob/b1fd47bda2a7a7d25091197b125c0adf82af6757/src/Type/Constant/ConstantArrayType.php#L209
     */
    private function findTypeAndMethodName(\PHPStan\Type\Constant\ConstantArrayType $constantArrayType) : ?\PHPStan\Type\Constant\ConstantArrayTypeAndMethod
    {
        if (!$this->areKeyTypesValid($constantArrayType)) {
            return null;
        }
        if (\count($constantArrayType->getValueTypes()) !== 2) {
            return null;
        }
        $classOrObjectType = $constantArrayType->getValueTypes()[0];
        $methodType = $constantArrayType->getValueTypes()[1];
        if (!$methodType instanceof \PHPStan\Type\Constant\ConstantStringType) {
            return \PHPStan\Type\Constant\ConstantArrayTypeAndMethod::createUnknown();
        }
        $objectWithoutClassType = new \PHPStan\Type\ObjectWithoutClassType();
        if ($classOrObjectType instanceof \PHPStan\Type\Constant\ConstantStringType) {
            $value = $classOrObjectType->getValue();
            if (!$this->reflectionProvider->hasClass($value)) {
                return \PHPStan\Type\Constant\ConstantArrayTypeAndMethod::createUnknown();
            }
            $classReflection = $this->reflectionProvider->getClass($value);
            $type = new \PHPStan\Type\ObjectType($classReflection->getName());
        } elseif ($objectWithoutClassType->isSuperTypeOf($classOrObjectType)->yes()) {
            $type = $classOrObjectType;
        } else {
            return \PHPStan\Type\Constant\ConstantArrayTypeAndMethod::createUnknown();
        }
        $trinaryLogic = $type->hasMethod($methodType->getValue());
        if (!$trinaryLogic->no()) {
            return \PHPStan\Type\Constant\ConstantArrayTypeAndMethod::createConcrete($type, $methodType->getValue(), $trinaryLogic);
        }
        return null;
    }
    private function areKeyTypesValid(\PHPStan\Type\Constant\ConstantArrayType $constantArrayType) : bool
    {
        $keyTypes = $constantArrayType->getKeyTypes();
        if (\count($keyTypes) !== 2) {
            return \false;
        }
        if ($keyTypes[0]->isSuperTypeOf(new \PHPStan\Type\Constant\ConstantIntegerType(0))->no()) {
            return \false;
        }
        return !$keyTypes[1]->isSuperTypeOf(new \PHPStan\Type\Constant\ConstantIntegerType(1))->no();
    }
}
