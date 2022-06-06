<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
final class ReturnArrayClassMethodToYield
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
    public function __construct(string $type, string $method)
    {
        $this->type = $type;
        $this->method = $method;
        \Rector\Core\Validation\RectorAssert::className($type);
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
