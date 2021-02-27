<?php

declare(strict_types=1);

namespace Rector\Renaming\ValueObject;

use PHPStan\Type\ObjectType;

final class RenameProperty
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $oldProperty;

    /**
     * @var string
     */
    private $newProperty;

    public function __construct(string $type, string $oldProperty, string $newProperty)
    {
        $this->type = $type;
        $this->oldProperty = $oldProperty;
        $this->newProperty = $newProperty;
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->type);
    }

    public function getOldProperty(): string
    {
        return $this->oldProperty;
    }

    public function getNewProperty(): string
    {
        return $this->newProperty;
    }
}
