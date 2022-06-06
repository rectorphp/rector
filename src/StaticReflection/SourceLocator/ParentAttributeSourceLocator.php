<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\StaticReflection\SourceLocator;

use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\Stmt\Namespace_;
use RectorPrefix20220606\PHPStan\BetterReflection\Identifier\Identifier;
use RectorPrefix20220606\PHPStan\BetterReflection\Identifier\IdentifierType;
use RectorPrefix20220606\PHPStan\BetterReflection\Reflection\Reflection;
use RectorPrefix20220606\PHPStan\BetterReflection\Reflection\ReflectionClass;
use RectorPrefix20220606\PHPStan\BetterReflection\Reflector\ClassReflector;
use RectorPrefix20220606\PHPStan\BetterReflection\Reflector\Reflector;
use RectorPrefix20220606\PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
use RectorPrefix20220606\PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\Rector\Core\PhpParser\AstResolver;
use RectorPrefix20220606\Symfony\Contracts\Service\Attribute\Required;
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
            $class = $this->astResolver->resolveClassFromClassReflection($classReflection, $identifierName);
            if ($class === null) {
                return null;
            }
            $class->namespacedName = new FullyQualified($identifierName);
            $fakeLocatedSource = new LocatedSource('virtual', null);
            $classReflector = new ClassReflector($this);
            return ReflectionClass::createFromNode($classReflector, $class, $fakeLocatedSource, new Namespace_(new Name('Symfony\\Component\\DependencyInjection\\Attribute')));
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
