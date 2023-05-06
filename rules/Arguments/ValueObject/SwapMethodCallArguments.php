<?php

declare (strict_types=1);
namespace Rector\Arguments\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
final class SwapMethodCallArguments
{
    /**
     * @readonly
     * @var string
     */
    private $class;
    /**
     * @readonly
     * @var string
     */
    private $method;
    /**
     * @var array<int, int>
     * @readonly
     */
    private $order;
    /**
     * @param array<int, int> $order
     */
    public function __construct(string $class, string $method, array $order)
    {
        $this->class = $class;
        $this->method = $method;
        $this->order = $order;
        RectorAssert::className($class);
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->class);
    }
    public function getMethod() : string
    {
        return $this->method;
    }
    /**
     * @return array<int, int>
     */
    public function getOrder() : array
    {
        return $this->order;
    }
}
