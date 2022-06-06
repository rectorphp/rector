<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

final class PropertyAndClassMethodName
{
    /**
     * @readonly
     * @var string
     */
    private $propertyName;
    /**
     * @readonly
     * @var string
     */
    private $classMethodName;
    public function __construct(string $propertyName, string $classMethodName)
    {
        $this->propertyName = $propertyName;
        $this->classMethodName = $classMethodName;
    }
    public function getPropertyName() : string
    {
        return $this->propertyName;
    }
    public function getClassMethodName() : string
    {
        return $this->classMethodName;
    }
}
