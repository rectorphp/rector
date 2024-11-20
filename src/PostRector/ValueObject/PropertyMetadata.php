<?php

declare (strict_types=1);
namespace Rector\PostRector\ValueObject;

use PhpParser\Modifiers;
use PHPStan\Type\Type;
final class PropertyMetadata
{
    /**
     * @readonly
     */
    private string $name;
    /**
     * @readonly
     */
    private ?Type $type;
    /**
     * @readonly
     */
    private int $flags = Modifiers::PRIVATE;
    public function __construct(string $name, ?Type $type, int $flags = Modifiers::PRIVATE)
    {
        $this->name = $name;
        $this->type = $type;
        $this->flags = $flags;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getType() : ?Type
    {
        return $this->type;
    }
    public function getFlags() : int
    {
        return $this->flags;
    }
}
