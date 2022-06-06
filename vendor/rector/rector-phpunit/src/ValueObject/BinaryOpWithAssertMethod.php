<?php

declare (strict_types=1);
namespace Rector\PHPUnit\ValueObject;

final class BinaryOpWithAssertMethod
{
    /**
     * @readonly
     * @var string
     */
    private $binaryOpClass;
    /**
     * @readonly
     * @var string
     */
    private $assetMethodName;
    /**
     * @readonly
     * @var string
     */
    private $notAssertMethodName;
    public function __construct(string $binaryOpClass, string $assetMethodName, string $notAssertMethodName)
    {
        $this->binaryOpClass = $binaryOpClass;
        $this->assetMethodName = $assetMethodName;
        $this->notAssertMethodName = $notAssertMethodName;
    }
    public function getBinaryOpClass() : string
    {
        return $this->binaryOpClass;
    }
    public function getAssetMethodName() : string
    {
        return $this->assetMethodName;
    }
    public function getNotAssertMethodName() : string
    {
        return $this->notAssertMethodName;
    }
}
