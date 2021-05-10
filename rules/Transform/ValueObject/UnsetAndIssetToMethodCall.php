<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
final class UnsetAndIssetToMethodCall
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $issetMethodCall;
    /**
     * @var string
     */
    private $unsedMethodCall;
    public function __construct(string $type, string $issetMethodCall, string $unsedMethodCall)
    {
        $this->type = $type;
        $this->issetMethodCall = $issetMethodCall;
        $this->unsedMethodCall = $unsedMethodCall;
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->type);
    }
    public function getIssetMethodCall() : string
    {
        return $this->issetMethodCall;
    }
    public function getUnsedMethodCall() : string
    {
        return $this->unsedMethodCall;
    }
}
