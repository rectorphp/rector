<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
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
        RectorAssert::className($newType);
        RectorAssert::className($serviceType);
        RectorAssert::methodName($serviceMethod);
    }
    public function getNewObjectType() : ObjectType
    {
        return new ObjectType($this->newType);
    }
    public function getServiceObjectType() : ObjectType
    {
        return new ObjectType($this->serviceType);
    }
    public function getServiceMethod() : string
    {
        return $this->serviceMethod;
    }
}
