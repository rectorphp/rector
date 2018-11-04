<?php declare(strict_types=1);

namespace Rector\Rector;

use Rector\PhpParser\Node\Builder\ClassPropertyCollector;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait ClassPropertyCollectorTrait
{
    /**
     * @var ClassPropertyCollector
     */
    private $classPropertyCollector;

    /**
     * @required
     */
    public function setClassPropertyCollector(ClassPropertyCollector $classPropertyCollector): void
    {
        $this->classPropertyCollector = $classPropertyCollector;
    }

    public function addPropertyToClass(string $class, string $propertyType, string $propertyName): void
    {
        $this->classPropertyCollector->addPropertyForClass($class, $propertyType, $propertyName);
    }
}
