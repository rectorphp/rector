<?php

declare (strict_types=1);
namespace Rector\Core\StaticReflection\SourceLocator;

use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PHPStan\BetterReflection\Reflector\ClassReflector;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use RectorPrefix202208\Symplify\Astral\ValueObject\NodeBuilder\ClassBuilder;
/**
 * Inspired from \PHPStan\BetterReflection\SourceLocator\Type\StringSourceLocator
 */
final class RenamedClassesSourceLocator implements SourceLocator
{
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    public function __construct(RenamedClassesDataCollector $renamedClassesDataCollector)
    {
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
    }
    public function locateIdentifier(Reflector $reflector, Identifier $identifier) : ?Reflection
    {
        if (!$identifier->isClass()) {
            return null;
        }
        $identifierName = $identifier->getName();
        foreach ($this->renamedClassesDataCollector->getOldClasses() as $oldClass) {
            if ($identifierName !== $oldClass) {
                continue;
            }
            // inspired at https://github.com/phpstan/phpstan-src/blob/a9dd9af959fb0c1e0a09d4850f78e05e8dff3d91/src/Reflection/BetterReflection/BetterReflectionProvider.php#L220-L225
            return $this->createFakeReflectionClassFromClassName($oldClass);
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
    private function createFakeReflectionClassFromClassName(string $oldClass) : ReflectionClass
    {
        $classBuilder = new ClassBuilder($oldClass);
        $class = $classBuilder->getNode();
        $fakeLocatedSource = new LocatedSource('virtual', null);
        $classReflector = new ClassReflector($this);
        return ReflectionClass::createFromNode($classReflector, $class, $fakeLocatedSource);
    }
}
