<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
final class NewToStaticCall
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $staticCallClass;
    /**
     * @var string
     */
    private $staticCallMethod;
    public function __construct(string $type, string $staticCallClass, string $staticCallMethod)
    {
        $this->type = $type;
        $this->staticCallClass = $staticCallClass;
        $this->staticCallMethod = $staticCallMethod;
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
