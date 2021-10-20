<?php

declare (strict_types=1);
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
use RectorPrefix20211020\Symfony\Contracts\Service\Attribute\Required;
/**
 * This mimics classes that PHPStan fails to find in scope, but actually has an access in static reflection.
 * Some weird bug, that crashes on parent classes.
 *
 * @see https://github.com/rectorphp/rector-src/pull/368/
 */
final class ParentAttributeSourceLocator implements \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
{
    /**
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @required
     */
    public function autowireParentAttributeSourceLocator(\Rector\Core\PhpParser\AstResolver $astResolver) : void
    {
        $this->astResolver = $astResolver;
    }
    public function locateIdentifier(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\Identifier $identifier) : ?\PHPStan\BetterReflection\Reflection\Reflection
    {
        if ($identifier->getName() === 'Symfony\\Component\\DependencyInjection\\Attribute\\Autoconfigure') {
            if ($this->reflectionProvider->hasClass($identifier->getName())) {
                $classReflection = $this->reflectionProvider->getClass($identifier->getName());
                $class = $this->astResolver->resolveClassFromClassReflection($classReflection, $identifier->getName());
                if ($class === null) {
                    return null;
                }
                $class->namespacedName = new \PhpParser\Node\Name\FullyQualified($identifier->getName());
                $fakeLocatedSource = new \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource('virtual', null);
                $classReflector = new \PHPStan\BetterReflection\Reflector\ClassReflector($this);
                return \PHPStan\BetterReflection\Reflection\ReflectionClass::createFromNode($classReflector, $class, $fakeLocatedSource, new \PhpParser\Node\Stmt\Namespace_(new \PhpParser\Node\Name('Symfony\\Component\\DependencyInjection\\Attribute')));
            }
        }
        return null;
    }
    public function locateIdentifiersByType(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\IdentifierType $identifierType) : array
    {
        return [];
    }
}
