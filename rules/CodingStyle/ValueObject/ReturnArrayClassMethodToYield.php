<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodingStyle\ValueObject;

use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Validation\RectorAssert;
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
        RectorAssert::className($type);
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->type);
    }
    public function getMethod() : string
    {
        return $this->method;
    }
}
