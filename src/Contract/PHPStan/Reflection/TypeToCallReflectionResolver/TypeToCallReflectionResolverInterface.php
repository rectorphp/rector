<?php

declare (strict_types=1);
namespace Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;
/**
 * @template TType as Type
 */
interface TypeToCallReflectionResolverInterface
{
    /**
     * @param \PHPStan\Type\Type $type
     */
    public function supports($type) : bool;
    /**
     * @param \PHPStan\Type\Type $type
     * @return FunctionReflection|MethodReflection|null
     * @param \PHPStan\Analyser\Scope $scope
     */
    public function resolve($type, $scope);
}
