<?php

declare (strict_types=1);
namespace Rector\Core\StaticReflection\SourceLocator;

use PhpParser\Node\Name\FullyQualified;
use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PHPStan\BetterReflection\Reflector\ClassReflector;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\PhpParser\AstResolver;
use RectorPrefix202211\Symfony\Contracts\Service\Attribute\Required;
/**
 * This mimics classes that PHPStan fails to find in scope, but actually has an access in static reflection.
 * Some weird bug, that crashes on parent classes.
 *
 * @see https://github.com/rectorphp/rector-src/pull/368/
 */
final class ParentAttributeSourceLocator implements SourceLocator
{
    /**
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @required
     */
    public function autowire(AstResolver $astResolver) : void
    {
        $this->astResolver = $astResolver;
    }
    public function locateIdentifier(Reflector $reflector, Identifier $identifier) : ?Reflection
    {
        $identifierName = $identifier->getName();
        if ($identifierName === 'Symfony\\Component\\DependencyInjection\\Attribute\\Autoconfigure' && $this->reflectionProvider->hasClass($identifierName)) {
            $classReflection = $this->reflectionProvider->getClass($identifierName);
            $class = $this->astResolver->resolveClassFromClassReflection($classReflection);
            if ($class === null) {
                return null;
            }
            $class->namespacedName = new FullyQualified($identifierName);
            $fakeLocatedSource = new LocatedSource('virtual', null);
            $classReflector = new ClassReflector($this);
            return ReflectionClass::createFromNode($classReflector, $class, $fakeLocatedSource, 'Symfony\\Component\\DependencyInjection\\Attribute');
        }
        return null;
    }
    /**
     * @return array<int, Reflection>
     */
    public function locateIdentifiersByType(Reflector $reflector, IdentifierType $identifierType) : array
    {
        return [];
    }
}
