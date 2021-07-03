<?php

declare(strict_types=1);

namespace Rector\Core\StaticReflection\SourceLocator;

use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Namespace_;
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
use Symfony\Contracts\Service\Attribute\Required;

/**
 * This mimics classes that PHPStan fails to find in scope, but actually has an access in static reflection.
 * Some weird bug, that crashes on parent classes.
 *
 * @see https://github.com/rectorphp/rector-src/pull/368/
 */
final class ParentAttributeSourceLocator implements SourceLocator
{
    private AstResolver $astResolver;

    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    #[Required]
    public function autowireParentAttributeSourceLocator(AstResolver $astResolver): void
    {
        $this->astResolver = $astResolver;
    }

    public function locateIdentifier(Reflector $reflector, Identifier $identifier): ?Reflection
    {
        if ($identifier->getName() === 'Symfony\Component\DependencyInjection\Attribute\Autoconfigure') {
            if ($this->reflectionProvider->hasClass($identifier->getName())) {
                $classReflection = $this->reflectionProvider->getClass($identifier->getName());

                $class = $this->astResolver->resolveClassFromClassReflection(
                    $classReflection,
                    $identifier->getName()
                );
                if ($class === null) {
                    return null;
                }

                $class->namespacedName = new FullyQualified($identifier->getName());

                $fakeLocatedSource = new LocatedSource('virtual', null);

                $classReflector = new ClassReflector($this);
                return ReflectionClass::createFromNode(
                    $classReflector,
                    $class,
                    $fakeLocatedSource,
                    new Namespace_(new Name('Symfony\Component\DependencyInjection\Attribute'))
                );
            }
        }

        return null;
    }

    public function locateIdentifiersByType(Reflector $reflector, IdentifierType $identifierType): array
    {
        return [];
    }
}
