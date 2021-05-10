<?php

declare (strict_types=1);
namespace Rector\Visibility\ValueObject;

use PHPStan\Type\ObjectType;
final class ChangeConstantVisibility
{
    /**
     * @var string
     */
    private $class;
    /**
     * @var string
     */
    private $constant;
    /**
     * @var int
     */
    private $visibility;
    public function __construct(string $class, string $constant, int $visibility)
    {
        $this->class = $class;
        $this->constant = $constant;
        $this->visibility = $visibility;
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->class);
    }
    public function getConstant() : string
    {
        return $this->constant;
    }
    public function getVisibility() : int
    {
        return $this->visibility;
    }
}
