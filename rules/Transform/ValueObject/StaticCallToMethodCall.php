<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Type\ObjectType;
final class StaticCallToMethodCall
{
    /**
     * @readonly
     * @var string
     */
    private $staticClass;
    /**
     * @readonly
     * @var string
     */
    private $staticMethod;
    /**
     * @readonly
     * @var string
     */
    private $classType;
    /**
     * @readonly
     * @var string
     */
    private $methodName;
    public function __construct(string $staticClass, string $staticMethod, string $classType, string $methodName)
    {
        $this->staticClass = $staticClass;
        $this->staticMethod = $staticMethod;
        $this->classType = $classType;
        $this->methodName = $methodName;
    }
    public function getClassObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->classType);
    }
    public function getClassType() : string
    {
        return $this->classType;
    }
    public function getMethodName() : string
    {
        return $this->methodName;
    }
    public function isStaticCallMatch(\PhpParser\Node\Expr\StaticCall $staticCall) : bool
    {
        if (!$staticCall->class instanceof \PhpParser\Node\Name) {
            return \false;
        }
        $staticCallClassName = $staticCall->class->toString();
        if ($staticCallClassName !== $this->staticClass) {
            return \false;
        }
        if (!$staticCall->name instanceof \PhpParser\Node\Identifier) {
            return \false;
        }
        // all methods
        if ($this->staticMethod === '*') {
            return \true;
        }
        $staticCallMethodName = $staticCall->name->toString();
        return $staticCallMethodName === $this->staticMethod;
    }
}
