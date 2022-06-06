<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver;

use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Reflection\FunctionReflection;
use RectorPrefix20220606\PHPStan\Reflection\MethodReflection;
use RectorPrefix20220606\PHPStan\Type\Type;
/**
 * @template TType as Type
 */
interface TypeToCallReflectionResolverInterface
{
    public function supports(Type $type) : bool;
    /**
     * @param TType $type
     * @return FunctionReflection|MethodReflection|null
     */
    public function resolve(Type $type, Scope $scope);
}
