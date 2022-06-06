<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\StaticReflection\SourceLocator;

use RectorPrefix20220606\PHPStan\BetterReflection\Identifier\Identifier;
use RectorPrefix20220606\PHPStan\BetterReflection\Identifier\IdentifierType;
use RectorPrefix20220606\PHPStan\BetterReflection\Reflection\Reflection;
use RectorPrefix20220606\PHPStan\BetterReflection\Reflection\ReflectionClass;
use RectorPrefix20220606\PHPStan\BetterReflection\Reflector\ClassReflector;
use RectorPrefix20220606\PHPStan\BetterReflection\Reflector\Reflector;
use RectorPrefix20220606\PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
use RectorPrefix20220606\PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use RectorPrefix20220606\Rector\Core\Configuration\RenamedClassesDataCollector;
use RectorPrefix20220606\Symplify\Astral\ValueObject\NodeBuilder\ClassBuilder;
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
