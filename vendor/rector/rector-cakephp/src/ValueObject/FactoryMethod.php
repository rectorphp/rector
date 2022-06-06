<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CakePHP\ValueObject;

use RectorPrefix20220606\PHPStan\Type\ObjectType;
final class FactoryMethod
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
    private $method;
    /**
     * @readonly
     * @var string
     */
    private $newClass;
    /**
     * @readonly
     * @var int
     */
    private $position;
    public function __construct(string $type, string $method, string $newClass, int $position)
    {
        $this->type = $type;
        $this->method = $method;
        $this->newClass = $newClass;
        $this->position = $position;
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->type);
    }
    public function getMethod() : string
    {
        return $this->method;
    }
    public function getPosition() : int
    {
        return $this->position;
    }
    public function getNewClass() : string
    {
        return $this->newClass;
    }
}
