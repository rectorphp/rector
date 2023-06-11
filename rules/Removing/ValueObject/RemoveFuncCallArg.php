<?php

declare (strict_types=1);
namespace Rector\Removing\ValueObject;

use Rector\Core\Validation\RectorAssert;
final class RemoveFuncCallArg
{
    /**
     * @var string
     */
    private $function;
    /**
     * @var int
     */
    private $argumentPosition;
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
