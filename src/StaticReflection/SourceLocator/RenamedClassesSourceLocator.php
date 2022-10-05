<?php

declare (strict_types=1);
namespace Rector\Core\StaticReflection\SourceLocator;

use PhpParser\Builder\Class_;
use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use Rector\Core\Configuration\RenamedClassesDataCollector;
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
        $classBuilder = new Class_($oldClass);
        $class = $classBuilder->getNode();
        return ReflectionClass::createFromInstance($class);
    }
}
