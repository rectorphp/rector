<?php declare(strict_types=1);

namespace Rector\BetterReflection\Reflector;

use phpDocumentor\Reflection\Types\Object_;
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

        if ($methodReflection) {
            $returnType = $methodReflection->getReturnType();

            if ($returnType) {
                return (string) $returnType;
            }

            $returnTypes = $methodReflection->getDocBlockReturnTypes();

            if (! isset($returnTypes[0])) {
                return null;
            }

            if ($returnTypes[0] instanceof Object_) {
                return ltrim((string) $returnTypes[0]->getFqsen(), '\\');
            }
        }

        return null;
    }
}
