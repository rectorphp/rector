<?php

declare (strict_types=1);
namespace RectorPrefix202608\Entropy\Reflection;

use RectorPrefix202608\Entropy\Attributes\RelatedTest;
use RectorPrefix202608\Entropy\Container\Exception\CreateServiceException;
use RectorPrefix202608\Entropy\Tests\Reflection\ParameterTypesResolver\ParameterTypesResolverTest;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
final class ParameterTypesResolver
{
    /**
     * @param ReflectionParameter[] $reflectionParameters
     * @param class-string $class
     *
     * @return array<class-string|class-string[]>
     */
    public static function resolve(ReflectionMethod $reflectionMethod, array $reflectionParameters, string $class): array
    {
        $parameterTypes = [];
        foreach ($reflectionParameters as $reflectionParameter) {
            $parameterType = $reflectionParameter->getType();
            if ($parameterType instanceof ReflectionNamedType && !$parameterType->isBuiltin()) {
                /** @var class-string $parameterTypeClass */
                $parameterTypeClass = $parameterType->getName();
                $parameterTypes[] = $parameterTypeClass;
                // skip default value as not required
            } elseif ($reflectionParameter->isDefaultValueAvailable()) {
                continue;
            } else {
                // try resolving array of class types via docblock
                if ($parameterType instanceof ReflectionNamedType && (string) $reflectionParameter->getType() === 'array') {
                    $docComment = $reflectionMethod->getDocComment();
                    if ($docComment !== \false) {
                        $pattern = sprintf('/@param\s+%s\[\]\s+\$%s/', '([\\\\\\w]+)', $reflectionParameter->getName());
                        if (preg_match($pattern, $docComment, $matches) === 1) {
                            // nested parameter types
                            $shortName = $matches[1];
                            $classReflection = $reflectionMethod->getDeclaringClass();
                            // match with "use ..." statements
                            $uses = UseStatementsResolver::resolve((string) $classReflection->getFileName());
                            if (array_key_exists($shortName, $uses)) {
                                $fullClassName = $uses[$shortName];
                            } else {
                                // same namespace
                                $fullClassName = $classReflection->getNamespaceName() . '\\' . $shortName;
                            }
                            /** @var class-string $fullClassName */
                            $parameterTypes[] = [$fullClassName];
                            continue;
                        }
                    }
                }
                // cannot resolve non-class parameter
                throw new CreateServiceException(sprintf('Cannot resolve parameter "%s" for class "%s"', $reflectionParameter->getName(), $class));
            }
        }
        return $parameterTypes;
    }
}
