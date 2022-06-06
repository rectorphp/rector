<?php

declare (strict_types=1);
namespace Rector\Renaming\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
final class RenameProperty
{
    /**
     * @readonly
     * @var string
     */
    private $type;
    /**
     * @readonly
     * @var string
     */
    private $oldProperty;
    /**
     * @readonly
     * @var string
     */
    private $newProperty;
    public function __construct(string $type, string $oldProperty, string $newProperty)
    {
        $this->type = $type;
        $this->oldProperty = $oldProperty;
        $this->newProperty = $newProperty;
        \Rector\Core\Validation\RectorAssert::className($type);
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->type);
    }
    public function getOldProperty() : string
    {
        return $this->oldProperty;
    }
    public function getNewProperty() : string
    {
        return $this->newProperty;
    }
}
