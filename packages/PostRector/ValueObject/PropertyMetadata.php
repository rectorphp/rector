<?php

declare (strict_types=1);
namespace Rector\PostRector\ValueObject;

use PHPStan\Type\Type;
final class PropertyMetadata
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var \PHPStan\Type\Type|null
     */
    private $type;
    /**
     * @var int
     */
    private $flags;
    public function __construct(string $name, ?\PHPStan\Type\Type $type, int $flags)
    {
        $this->name = $name;
        $this->type = $type;
        $this->flags = $flags;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getType() : ?\PHPStan\Type\Type
    {
        return $this->type;
    }
    public function getFlags() : int
    {
        return $this->flags;
    }
}
