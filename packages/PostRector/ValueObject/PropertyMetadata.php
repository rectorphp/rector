<?php

declare (strict_types=1);
namespace Rector\PostRector\ValueObject;

use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\Type;
final class PropertyMetadata
{
    /**
     * @readonly
     * @var string
     */
    private $name;
    /**
     * @readonly
     * @var \PHPStan\Type\Type|null
     */
    private $type;
    /**
     * @readonly
     * @var int
     */
    private $flags = Class_::MODIFIER_PRIVATE;
    public function __construct(string $name, ?Type $type, int $flags = Class_::MODIFIER_PRIVATE)
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
