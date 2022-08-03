<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
final class NewToStaticCall
{
    /**
     * @readonly
     * @var string
     */
    private $type;
    /**
     * @var class-string
     * @readonly
     */
    private $staticCallClass;
    /**
     * @readonly
     * @var string
     */
    private $staticCallMethod;
    /**
     * @param class-string $staticCallClass
     */
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
