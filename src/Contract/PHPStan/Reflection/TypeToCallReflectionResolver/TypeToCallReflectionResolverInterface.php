<?php

declare (strict_types=1);
namespace Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;
interface TypeToCallReflectionResolverInterface
{
    public function supports(\PHPStan\Type\Type $type) : bool;
    /**
     * @return FunctionReflection|MethodReflection|null
     */
    public function resolve(\PHPStan\Type\Type $type, \PHPStan\Analyser\Scope $scope);
}
