<?php

declare (strict_types=1);
namespace Rector\PHPUnit\ValueObject;

final class FunctionNameWithAssertMethods
{
    /**
     * @readonly
     */
    private string $functionName;
    /**
     * @readonly
     */
    private string $assetMethodName;
    /**
     * @readonly
     */
    private string $notAssertMethodName;
    public function __construct(string $functionName, string $assetMethodName, string $notAssertMethodName)
    {
        $this->functionName = $functionName;
        $this->assetMethodName = $assetMethodName;
        $this->notAssertMethodName = $notAssertMethodName;
    }
    public function getFunctionName() : string
    {
        return $this->functionName;
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
