<?php declare(strict_types=1);

namespace Rector\BetterReflection\Reflector;

use Rector\BetterReflection\SourceLocator\SourceLocatorFactory;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\Reflector\Exception\IdentifierNotFound;

final class MethodReflector
{
    /**
     * @var ClassReflector
     */
    private $classReflector;

    public function __construct(ClassReflector $classReflector)
    {
        $this->classReflector = $classReflector;
    }

    public function reflectClassMethod(string $class, string $method): ?ReflectionMethod
    {
        try {
            $classReflection = $this->classReflector->reflect($class);
        } catch (IdentifierNotFound $identifierNotFoundException) {
            // class doesn't exist
            return null;
        }

        return $classReflection->getImmediateMethods()[$method] ?? null;
    }
}
