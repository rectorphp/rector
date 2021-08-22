<?php

declare (strict_types=1);
namespace Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;
use Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverInterface;
final class TypeToCallReflectionResolverRegistry
{
    /**
     * @var \Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverInterface[]
     */
    private $resolvers;
    /**
     * @param TypeToCallReflectionResolverInterface[] $resolvers
     */
    public function __construct(array $resolvers)
    {
        $this->resolvers = $resolvers;
    }
    /**
     * @return FunctionReflection|MethodReflection|null
     */
    public function resolve(\PHPStan\Type\Type $type, \PHPStan\Analyser\Scope $scope)
    {
        foreach ($this->resolvers as $resolver) {
            if (!$resolver->supports($type)) {
                continue;
            }
            return $resolver->resolve($type, $scope);
        }
        return null;
    }
}
