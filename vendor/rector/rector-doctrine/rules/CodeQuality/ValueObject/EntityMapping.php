<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\ValueObject;

use PhpParser\Node\Stmt\Property;
use RectorPrefix202401\Webmozart\Assert\Assert;
final class EntityMapping
{
    /**
     * @readonly
     * @var string
     */
    private $className;
    /**
     * @var array<string, mixed>
     */
    private $entityMapping;
    /**
     * @param array<string, mixed> $propertyMapping
     */
    public function __construct(string $className, array $propertyMapping)
    {
        $this->className = $className;
        Assert::allString(\array_keys($propertyMapping));
        $this->entityMapping = $propertyMapping;
    }
    public function getClassName() : string
    {
        return $this->className;
    }
    /**
     * @return mixed[]|null
     */
    public function matchFieldPropertyMapping(Property $property) : ?array
    {
        $propertyName = $this->getPropertyName($property);
        return $this->entityMapping['fields'][$propertyName] ?? null;
    }
    /**
     * @return mixed[]|null
     */
    public function matchEmbeddedPropertyMapping(Property $property) : ?array
    {
        $propertyName = $this->getPropertyName($property);
        return $this->entityMapping['embedded'][$propertyName] ?? null;
    }
    /**
     * @return array<string, mixed>|null
     */
    public function matchManyToOnePropertyMapping(Property $property) : ?array
    {
        $propertyName = $this->getPropertyName($property);
        return $this->entityMapping['manyToOne'][$propertyName] ?? null;
    }
    /**
     * @return array<string, mixed>|null
     */
    public function matchOneToManyPropertyMapping(Property $property) : ?array
    {
        $propertyName = $this->getPropertyName($property);
        return $this->entityMapping['oneToMany'][$propertyName] ?? null;
    }
    /**
     * @return array<string, mixed>
     */
    public function getClassMapping() : array
    {
        $classMapping = $this->entityMapping;
        unset($classMapping['fields']);
        unset($classMapping['oneToMany']);
        return $classMapping;
    }
    /**
     * @return array<string, mixed>|null
     */
    public function matchIdPropertyMapping(Property $property) : ?array
    {
        $propertyName = $this->getPropertyName($property);
        return $this->entityMapping['id'][$propertyName] ?? null;
    }
    private function getPropertyName(Property $property) : string
    {
        return $property->props[0]->name->toString();
    }
}
