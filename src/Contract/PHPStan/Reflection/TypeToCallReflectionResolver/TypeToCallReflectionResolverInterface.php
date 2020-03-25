<?php

declare(strict_types=1);

namespace Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver;

use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;

interface TypeToCallReflectionResolverInterface
{
    public function supports(Type $type): bool;

    /**
     * @return FunctionReflection|MethodReflection|null
     */
    public function resolve(Type $type, ClassMemberAccessAnswerer $classMemberAccessAnswerer);
}
