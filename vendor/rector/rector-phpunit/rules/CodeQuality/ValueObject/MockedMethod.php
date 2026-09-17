<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\ValueObject;

use PHPStan\Type\IntersectionType;
final class MockedMethod
{
    /**
     * @readonly
     */
    private string $methodName;
    /**
     * @readonly
     */
    private IntersectionType $intersectionType;
    public function __construct(string $methodName, IntersectionType $intersectionType)
    {
        $this->methodName = $methodName;
        $this->intersectionType = $intersectionType;
    }
    public function getMethodName(): string
    {
        return $this->methodName;
    }
    public function getCallerType(): IntersectionType
    {
        return $this->intersectionType;
    }
}
