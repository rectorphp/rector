<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver;

use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Reflection\MethodReflection;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverInterface;
use RectorPrefix20220606\Rector\Core\ValueObject\MethodName;
/**
 * @see https://github.com/phpstan/phpstan-src/blob/b1fd47bda2a7a7d25091197b125c0adf82af6757/src/Type/ObjectType.php#L705
 *
 * @implements TypeToCallReflectionResolverInterface<ObjectType>
 */
final class ObjectTypeToCallReflectionResolver implements TypeToCallReflectionResolverInterface
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function supports(Type $type) : bool
    {
        return $type instanceof ObjectType;
    }
    /**
     * @param ObjectType $type
     */
    public function resolve(Type $type, Scope $scope) : ?MethodReflection
    {
        $className = $type->getClassName();
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if (!$classReflection->hasNativeMethod(MethodName::INVOKE)) {
            return null;
        }
        return $classReflection->getNativeMethod(MethodName::INVOKE);
    }
}
