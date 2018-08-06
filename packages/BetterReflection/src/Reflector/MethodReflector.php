<?php declare(strict_types=1);

namespace Rector\BetterReflection\Reflector;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\Static_;
use phpDocumentor\Reflection\Types\This;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Throwable;

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
        $classReflection = $this->smartClassReflector->reflect($class);
        if ($classReflection === null) {
            return null;
        }

        return $classReflection->hasMethod($method) ? $classReflection->getMethod($method) : null;
    }

    /**
     * @todo possibly cache, quite slow
     * @return string[]
     */
    public function getMethodReturnTypes(string $class, string $methodCallName): array
    {
        $methodReflection = $this->reflectClassMethod($class, $methodCallName);
        if (! $methodReflection) {
            return [];
        }

        $returnType = $methodReflection->getReturnType();
        if ($returnType) {
            return [(string) $returnType];
        }

        try {
            return $this->resolveDocBlockReturnTypes($class, $methodReflection->getDocBlockReturnTypes());
        } catch (Throwable $throwable) {
            // fails on PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface + @return array<string, mixed>
            // with error "\PhpCsFixer\FixerConfiguration\array<string," is not a valid Fqsen."
            return [];
        }
    }

    /**
     * @param string[]|Type[] $returnTypes
     * @return string[]
     */
    private function resolveDocBlockReturnTypes(string $class, array $returnTypes): array
    {
        if (! isset($returnTypes[0])) {
            return [];
        }

        $types = [];
        foreach ($returnTypes as $returnType) {
            if ($returnType instanceof Object_) {
                $types[] = ltrim((string) $returnType->getFqsen(), '\\');
            }

            if ($returnType instanceof Static_ || $returnType instanceof Self_ || $returnType instanceof This) {
                $types[] = $class;
            }
        }

        return $this->completeParentClasses($types);
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    private function resolveFirstMatchingTypeAndMethod(array $types, string $method): array
    {
        foreach ($types as $type) {
            $returnTypes = $this->getMethodReturnTypes($type, $method);
            if ($returnTypes) {
                return $this->completeParentClasses($returnTypes);
            }
        }

        return [];
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    private function completeParentClasses(array $types): array
    {
        foreach ($types as $type) {
            $types = array_merge($types, $this->smartClassReflector->getClassParents($type));
        }

        return $types;
    }
}
