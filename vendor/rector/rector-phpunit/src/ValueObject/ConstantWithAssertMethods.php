<?php

declare (strict_types=1);
namespace Rector\PHPUnit\ValueObject;

final class ConstantWithAssertMethods
{
    /**
     * @readonly
     */
    private string $constant;
    /**
     * @readonly
     */
    private string $assetMethodName;
    /**
     * @readonly
     */
    private string $notAssertMethodName;
    public function __construct(string $constant, string $assetMethodName, string $notAssertMethodName)
    {
        $this->constant = $constant;
        $this->assetMethodName = $assetMethodName;
        $this->notAssertMethodName = $notAssertMethodName;
    }
    public function getConstant() : string
    {
        return $this->constant;
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
