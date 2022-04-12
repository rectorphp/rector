<?php

declare (strict_types=1);
namespace Rector\Doctrine\ValueObject;

use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
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
    public function __construct(string $propertyName, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo)
    {
        $this->propertyName = $propertyName;
        $this->phpDocInfo = $phpDocInfo;
    }
    public function getPropertyName() : string
    {
        return $this->propertyName;
    }
    public function getPhpDocInfo() : \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
    {
        return $this->phpDocInfo;
    }
}
