<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Transform\ValueObject;

use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Validation\RectorAssert;
final class NewArgToMethodCall
{
    /**
     * @readonly
     * @var string
     */
    private $type;
    /**
     * @var mixed
     */
    private $value;
    /**
     * @readonly
     * @var string
     */
    private $methodCall;
    /**
     * @param mixed $value
     */
    public function __construct(string $type, $value, string $methodCall)
    {
        $this->type = $type;
        $this->value = $value;
        $this->methodCall = $methodCall;
        RectorAssert::className($type);
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->type);
    }
    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
    public function getMethodCall() : string
    {
        return $this->methodCall;
    }
}
