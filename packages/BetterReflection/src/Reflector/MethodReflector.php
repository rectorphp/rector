<?php declare(strict_types=1);

namespace Rector\BetterReflection\Reflector;

use Nette\Utils\Strings;
use Rector\BetterReflection\Reflection\ReflectionMethod;
use Rector\BetterReflection\Reflector\Exception\IdentifierNotFound;
use TypeError;

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
        } catch (TypeError $typeError) {
            // temp bug workaround
            if (Strings::contains($typeError->getMessage(), 'SourceStubber::addDocComment()')) {
                return null;
            }
        }

        return $classReflection->getImmediateMethods()[$method] ?? null;
    }
}
