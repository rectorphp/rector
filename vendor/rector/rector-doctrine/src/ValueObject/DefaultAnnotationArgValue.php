<?php

declare (strict_types=1);
namespace Rector\Doctrine\ValueObject;

final class DefaultAnnotationArgValue
{
    /**
     * @readonly
     * @var string
     */
    private $annotationClass;
    /**
     * @readonly
     * @var string
     */
    private $argName;
    /**
     * @readonly
     * @var bool|int|string
     */
    private $defaultValue;
    /**
     * @param bool|int|string $defaultValue
     */
    public function __construct(string $annotationClass, string $argName, $defaultValue)
    {
        $this->annotationClass = $annotationClass;
        $this->argName = $argName;
        $this->defaultValue = $defaultValue;
    }
    public function getAnnotationClass() : string
    {
        return $this->annotationClass;
    }
    public function getArgName() : string
    {
        return $this->argName;
    }
    /**
     * @return bool|int|string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
