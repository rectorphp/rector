<?php

declare (strict_types=1);
namespace Rector\Removing\ValueObject;

use Rector\Validation\RectorAssert;
final class RemoveFuncCallArg
{
    /**
     * @readonly
     */
    private string $function;
    /**
     * @readonly
     */
    private int $argumentPosition;
    public function __construct(string $function, int $argumentPosition)
    {
        $this->function = $function;
        $this->argumentPosition = $argumentPosition;
        RectorAssert::functionName($function);
    }
    public function getFunction() : string
    {
        return $this->function;
    }
    public function getArgumentPosition() : int
    {
        return $this->argumentPosition;
    }
}
