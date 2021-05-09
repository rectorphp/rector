<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
final class ServiceGetterToConstructorInjection
{
    /**
     * @var string
     */
    private $oldType;
    /**
     * @var string
     */
    private $oldMethod;
    /**
     * @var string
     */
    private $serviceType;
    public function __construct(string $oldType, string $oldMethod, string $serviceType)
    {
        $this->oldType = $oldType;
        $this->oldMethod = $oldMethod;
        $this->serviceType = $serviceType;
    }
    public function getOldObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->oldType);
    }
    public function getOldMethod() : string
    {
        return $this->oldMethod;
    }
    public function getServiceType() : string
    {
        return $this->serviceType;
    }
}
