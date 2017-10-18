<?php declare(strict_types=1);

namespace Rector\BetterReflection\Reflector;

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
            // class doesn't exist
            return null;
        }

        if ($classReflection === null) {
            return null;
        }

        return $classReflection->getImmediateMethods()[$method] ?? null;
    }
}
