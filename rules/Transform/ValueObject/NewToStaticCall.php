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
     * @readonly
     * @var string
     */
    private $staticCallClass;
    /**
     * @readonly
     * @var string
     */
    private $staticCallMethod;
    public function __construct(string $type, string $staticCallClass, string $staticCallMethod)
    {
        $this->type = $type;
        $this->staticCallClass = $staticCallClass;
        $this->staticCallMethod = $staticCallMethod;
        \Rector\Core\Validation\RectorAssert::className($type);
        \Rector\Core\Validation\RectorAssert::className($staticCallClass);
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->type);
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
