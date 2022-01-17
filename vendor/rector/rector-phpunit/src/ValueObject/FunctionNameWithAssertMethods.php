<?php

declare (strict_types=1);
namespace Rector\PHPUnit\ValueObject;

final class FunctionNameWithAssertMethods
{
    /**
     * @readonly
     * @var string
     */
    private $functionName;
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
