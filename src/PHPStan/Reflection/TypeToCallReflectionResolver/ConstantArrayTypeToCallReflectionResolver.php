<?php

declare(strict_types=1);

namespace Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver;

use PHPStan\Reflection\ClassMemberAccessAnswerer;
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
 */
final class ConstantArrayTypeToCallReflectionResolver implements TypeToCallReflectionResolverInterface
{
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function supports(Type $type): bool
    {
        return $type instanceof ConstantArrayType;
    }

    /**
     * @param ConstantArrayType $type
     */
    public function resolve(Type $type, ClassMemberAccessAnswerer $classMemberAccessAnswerer)
    {
        $typeAndMethodName = $this->findTypeAndMethodName($type);
        if ($typeAndMethodName === null) {
            return null;
        }

        if ($typeAndMethodName->isUnknown() || ! $typeAndMethodName->getCertainty()->yes()) {
            return null;
        }

        $method = $typeAndMethodName
            ->getType()
            ->getMethod($typeAndMethodName->getMethod(), $classMemberAccessAnswerer);

        if (! $classMemberAccessAnswerer->canCallMethod($method)) {
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

        return ! $keyTypes[1]->isSuperTypeOf(new ConstantIntegerType(1))->no();
    }
}
