<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
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
        \Rector\Core\Validation\RectorAssert::className($type);
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->type);
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
