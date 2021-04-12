<?php

declare(strict_types=1);
namespace Rector\Tests\Autodiscovery\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector\Source\ValueObject;

class PrimitiveValueObject
{
    /**
     * @var string
     */
    private $name;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    public function getName(): string
    {
        return $this->name;
    }
}
