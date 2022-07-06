<?php

declare (strict_types=1);
namespace Rector\Doctrine\ValueObject;

final class PropertyNamesAndPhpDocInfos
{
    /**
     * @var PropertyNameAndPhpDocInfo[]
     * @readonly
     */
    private $propertyNameAndPhpDocInfos;
    /**
     * @param PropertyNameAndPhpDocInfo[] $propertyNameAndPhpDocInfos
     */
    public function __construct(array $propertyNameAndPhpDocInfos)
    {
        $this->propertyNameAndPhpDocInfos = $propertyNameAndPhpDocInfos;
    }
    /**
     * @return string[]
     */
    public function getPropertyNames() : array
    {
        $propertyNames = [];
        foreach ($this->propertyNameAndPhpDocInfos as $propertyNameAndPhpDocInfo) {
            $propertyNames[] = $propertyNameAndPhpDocInfo->getPropertyName();
        }
        return $propertyNames;
    }
    /**
     * @return PropertyNameAndPhpDocInfo[]
     */
    public function all() : array
    {
        return $this->propertyNameAndPhpDocInfos;
    }
}
