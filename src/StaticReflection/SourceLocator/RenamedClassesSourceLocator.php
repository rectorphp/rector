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
use RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\ClassBuilder;
/**
 * Inspired from \PHPStan\BetterReflection\SourceLocator\Type\StringSourceLocator
 */
final class RenamedClassesSourceLocator implements \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
{
    /**
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    public function __construct(\Rector\Core\Configuration\RenamedClassesDataCollector $renamedClassesDataCollector)
    {
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
    }
    public function locateIdentifier(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\Identifier $identifier) : ?\PHPStan\BetterReflection\Reflection\Reflection
    {
        foreach ($this->renamedClassesDataCollector->getOldToNewClasses() as $oldClass => $newClass) {
            if ($identifier->getName() !== $oldClass) {
                continue;
            }
            // inspired at https://github.com/phpstan/phpstan-src/blob/a9dd9af959fb0c1e0a09d4850f78e05e8dff3d91/src/Reflection/BetterReflection/BetterReflectionProvider.php#L220-L225
            return $this->createFakeReflectionClassFromClassName($oldClass);
        }
        return null;
    }
    public function locateIdentifiersByType(\PHPStan\BetterReflection\Reflector\Reflector $reflector, \PHPStan\BetterReflection\Identifier\IdentifierType $identifierType) : array
    {
        return [];
    }
    private function createFakeReflectionClassFromClassName(string $oldClass) : \PHPStan\BetterReflection\Reflection\ReflectionClass
    {
        $classBuilder = new \RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\ClassBuilder($oldClass);
        $class = $classBuilder->getNode();
        $fakeLocatedSource = new \PHPStan\BetterReflection\SourceLocator\Located\LocatedSource('virtual', null);
        $classReflector = new \PHPStan\BetterReflection\Reflector\ClassReflector($this);
        return \PHPStan\BetterReflection\Reflection\ReflectionClass::createFromNode($classReflector, $class, $fakeLocatedSource);
    }
}
