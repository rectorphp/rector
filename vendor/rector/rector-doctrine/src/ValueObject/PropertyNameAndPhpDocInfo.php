<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\ValueObject;

use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
final class PropertyNameAndPhpDocInfo
{
    /**
     * @readonly
     * @var string
     */
    private $propertyName;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
     */
    private $phpDocInfo;
    public function __construct(string $propertyName, PhpDocInfo $phpDocInfo)
    {
        $this->propertyName = $propertyName;
        $this->phpDocInfo = $phpDocInfo;
    }
    public function getPropertyName() : string
    {
        return $this->propertyName;
    }
    public function getPhpDocInfo() : PhpDocInfo
    {
        return $this->phpDocInfo;
    }
}
