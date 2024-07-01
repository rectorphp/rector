<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\ValueObject;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use Rector\Exception\ShouldNotHappenException;
use RectorPrefix202407\Webmozart\Assert\Assert;
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
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $property
     */
    public function matchFieldPropertyMapping($property) : ?array
    {
        $propertyName = $this->getPropertyName($property);
        return $this->entityMapping['fields'][$propertyName] ?? null;
    }
    /**
     * @return mixed[]|null
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $property
     */
    public function matchEmbeddedPropertyMapping($property) : ?array
    {
        $propertyName = $this->getPropertyName($property);
        return $this->entityMapping['embedded'][$propertyName] ?? null;
    }
    /**
     * @return array<string, mixed>|null
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $property
     */
    public function matchManyToManyPropertyMapping($property) : ?array
    {
        $propertyName = $this->getPropertyName($property);
        return $this->entityMapping['manyToMany'][$propertyName] ?? null;
    }
    /**
     * @return array<string, mixed>|null
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $property
     */
    public function matchManyToOnePropertyMapping($property) : ?array
    {
        $propertyName = $this->getPropertyName($property);
        return $this->entityMapping['manyToOne'][$propertyName] ?? null;
    }
    /**
     * @return array<string, mixed>|null
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $property
     */
    public function matchOneToManyPropertyMapping($property) : ?array
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
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $property
     */
    public function matchIdPropertyMapping($property) : ?array
    {
        $propertyName = $this->getPropertyName($property);
        return $this->entityMapping['id'][$propertyName] ?? null;
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $property
     */
    private function getPropertyName($property) : string
    {
        if ($property instanceof Property) {
            return $property->props[0]->name->toString();
        }
        if ($property->var instanceof Variable) {
            $paramName = $property->var->name;
            Assert::string($paramName);
            return $paramName;
        }
        throw new ShouldNotHappenException();
    }
}
