<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ValueObject;

use PHPStan\Type\ObjectType;
final class ReturnArrayClassMethodToYield
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $method;
    public function __construct(string $type, string $method)
    {
        $this->type = $type;
        $this->method = $method;
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->type);
    }
    public function getMethod() : string
    {
        return $this->method;
    }
}
