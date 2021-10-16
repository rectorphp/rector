<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\ValueObject\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
final class ShortenedObjectType extends \PHPStan\Type\ObjectType
{
    /**
     * @var class-string
     */
    private $fullyQualifiedName;
    /**
     * @param class-string $fullyQualifiedName
     */
    public function __construct(string $shortName, string $fullyQualifiedName)
    {
        $this->fullyQualifiedName = $fullyQualifiedName;
        parent::__construct($shortName);
    }
    public function isSuperTypeOf(\PHPStan\Type\Type $type) : \PHPStan\TrinaryLogic
    {
        $fullyQualifiedObjectType = new \PHPStan\Type\ObjectType($this->fullyQualifiedName);
        return $fullyQualifiedObjectType->isSuperTypeOf($type);
    }
    public function getShortName() : string
    {
        return $this->getClassName();
    }
    /**
     * @return class-string
     */
    public function getFullyQualifiedName() : string
    {
        return $this->fullyQualifiedName;
    }
}
