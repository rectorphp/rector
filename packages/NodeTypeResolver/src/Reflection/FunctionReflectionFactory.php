<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Reflection;

use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\FunctionReflectionFactory as FunctionReflectionFactoryInterface;
use PHPStan\Type\Type;
use ReflectionFunction;

final class FunctionReflectionFactory implements FunctionReflectionFactoryInterface
{
    /**
     * @param mixed[] $phpDocParameterTypes
     */
    public function create(
        ReflectionFunction $reflection,
        array $phpDocParameterTypes,
        ?Type $phpDocReturnType = null
    ): FunctionReflection {
        return new FunctionReflection($reflection, $phpDocParameterTypes, $phpDocReturnType);
    }
}
