<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Validation\RectorAssert;
final class NewToStaticCall
{
    /**
     * @readonly
     */
    private string $type;
    /**
     * @readonly
     */
    private string $staticCallClass;
    /**
     * @readonly
     */
    private string $staticCallMethod;
    public function __construct(string $type, string $staticCallClass, string $staticCallMethod)
    {
        $this->type = $type;
        $this->staticCallClass = $staticCallClass;
        $this->staticCallMethod = $staticCallMethod;
        RectorAssert::className($type);
        RectorAssert::className($staticCallClass);
        RectorAssert::methodName($staticCallMethod);
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->type);
    }
    public function getStaticCallClass() : string
    {
        return $this->staticCallClass;
    }
    public function getStaticCallMethod() : string
    {
        return $this->staticCallMethod;
    }
}
