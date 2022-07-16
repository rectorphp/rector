<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
final class ServiceGetterToConstructorInjection
{
    /**
     * @readonly
     * @var string
     */
    private $oldType;
    /**
     * @readonly
     * @var string
     */
    private $oldMethod;
    /**
     * @readonly
     * @var string
     */
    private $serviceType;
    public function __construct(string $oldType, string $oldMethod, string $serviceType)
    {
        $this->oldType = $oldType;
        $this->oldMethod = $oldMethod;
        $this->serviceType = $serviceType;
        RectorAssert::className($oldType);
        RectorAssert::methodName($oldMethod);
        RectorAssert::className($serviceType);
    }
    public function getOldObjectType() : ObjectType
    {
        return new ObjectType($this->oldType);
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
