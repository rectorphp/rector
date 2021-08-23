<?php

declare (strict_types=1);
namespace Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;
interface TypeToCallReflectionResolverInterface
{
    /**
     * @param \PHPStan\Type\Type $type
     */
    public function supports($type) : bool;
    /**
     * @return FunctionReflection|MethodReflection|null
     * @param \PHPStan\Type\Type $type
     * @param \PHPStan\Analyser\Scope $scope
     */
    public function resolve($type, $scope);
}
