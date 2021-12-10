<?php

declare (strict_types=1);
namespace Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverInterface;
use Rector\Core\ValueObject\MethodName;
/**
 * @see https://github.com/phpstan/phpstan-src/blob/b1fd47bda2a7a7d25091197b125c0adf82af6757/src/Type/ObjectType.php#L705
 *
 * @implements TypeToCallReflectionResolverInterface<ObjectType>
 */
final class ObjectTypeToCallReflectionResolver implements \Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverInterface
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
        return $type instanceof \PHPStan\Type\ObjectType;
    }
    /**
     * @param ObjectType $type
     */
    public function resolve(\PHPStan\Type\Type $type, \PHPStan\Analyser\Scope $scope) : ?\PHPStan\Reflection\MethodReflection
    {
        $className = $type->getClassName();
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if (!$classReflection->hasNativeMethod(\Rector\Core\ValueObject\MethodName::INVOKE)) {
            return null;
        }
        return $classReflection->getNativeMethod(\Rector\Core\ValueObject\MethodName::INVOKE);
    }
}
