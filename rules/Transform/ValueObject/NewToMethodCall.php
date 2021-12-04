<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
final class NewToMethodCall
{
    /**
     * @readonly
     * @var string
     */
    private $newType;
    /**
     * @readonly
     * @var string
     */
    private $serviceType;
    /**
     * @readonly
     * @var string
     */
    private $serviceMethod;
    public function __construct(string $newType, string $serviceType, string $serviceMethod)
    {
        $this->newType = $newType;
        $this->serviceType = $serviceType;
        $this->serviceMethod = $serviceMethod;
    }
    public function getNewObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->newType);
    }
    public function getServiceObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->serviceType);
    }
    public function getServiceMethod() : string
    {
        return $this->serviceMethod;
    }
}
