<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\ValueObject;

use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
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
     * @return PhpDocInfo[]
     */
    public function getPhpDocInfos() : array
    {
        $phpDocInfos = [];
        foreach ($this->propertyNameAndPhpDocInfos as $propertyNameAndPhpDocInfo) {
            $phpDocInfos[] = $propertyNameAndPhpDocInfo->getPhpDocInfo();
        }
        return $phpDocInfos;
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
