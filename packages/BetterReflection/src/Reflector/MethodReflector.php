<?php declare(strict_types=1);

namespace Rector\BetterReflection\Reflector;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\Static_;
use Rector\BetterReflection\Reflection\ReflectionMethod;
use Rector\BetterReflection\Reflector\Exception\IdentifierNotFound;

final class MethodReflector
{
    /**
     * @var SmartClassReflector
     */
    private $smartClassReflector;

    public function __construct(SmartClassReflector $smartClassReflector)
    {
        $this->smartClassReflector = $smartClassReflector;
    }

    public function reflectClassMethod(string $class, string $method): ?ReflectionMethod
    {
        try {
            $classReflection = $this->smartClassReflector->reflect($class);
        } catch (IdentifierNotFound $identifierNotFoundException) {
            return null;
        }

        if ($classReflection === null) {
            return null;
        }

        return $classReflection->getImmediateMethods()[$method] ?? null;
    }

    public function getMethodReturnType(string $class, string $methodCallName): ?string
    {
        $methodReflection = $this->reflectClassMethod($class, $methodCallName);
        if (! $methodReflection) {
            return null;
        }

        $returnType = $methodReflection->getReturnType();
        if ($returnType) {
            return (string) $returnType;
        }

        return $this->resolveDocBlockReturnTypes($class, $methodReflection->getDocBlockReturnTypes());
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    public function resolveReturnTypesForTypesAndMethod(array $types, string $method): array
    {
        if (! count($types)) {
            return [];
        }

        $returnType = $this->resolveFirstMatchingTypeAndMethod($types, $method);
        if ($returnType === $types[0]) { // self/static
            return $types;
        }

        return $returnType ? [$returnType] : [];
    }

    /**
     * @param string[]|Type[] $returnTypes
     */
    private function resolveDocBlockReturnTypes(string $class, array $returnTypes): ?string
    {
        if (! isset($returnTypes[0])) {
            return null;
        }

        // @todo: improve for multi types
        if ($returnTypes[0] instanceof Object_) {
            return ltrim((string) $returnTypes[0]->getFqsen(), '\\');
        }

        if ($returnTypes[0] instanceof Static_ || $returnTypes[0] instanceof Self_) {
            return $class;
        }

        return null;
    }

    /**
     * @param string[] $types
     */
    private function resolveFirstMatchingTypeAndMethod(array $types, string $method): ?string
    {
        $returnType = null;
        foreach ($types as $type) {
            $returnType = $this->getMethodReturnType($type, $method);
            if ($returnType) {
                return $returnType;
            }
        }

        return null;
    }
}
