<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject\InvokableController;

final class ActiveClassElements
{
    /**
     * @var string[]
     * @readonly
     */
    private $propertyNames;
    /**
     * @var string[]
     * @readonly
     */
    private $constantNames;
    /**
     * @var string[]
     * @readonly
     */
    private $methodNames;
    /**
     * @param string[] $propertyNames
     * @param string[] $constantNames
     * @param string[] $methodNames
     */
    public function __construct(array $propertyNames, array $constantNames, array $methodNames)
    {
        $this->propertyNames = $propertyNames;
        $this->constantNames = $constantNames;
        $this->methodNames = $methodNames;
    }
    public function hasPropertyName(string $propertyName) : bool
    {
        return \in_array($propertyName, $this->propertyNames, \true);
    }
    public function hasConstantName(string $constantName) : bool
    {
        return \in_array($constantName, $this->constantNames, \true);
    }
    public function hasMethodName(string $methodName) : bool
    {
        return \in_array($methodName, $this->methodNames, \true);
    }
}
