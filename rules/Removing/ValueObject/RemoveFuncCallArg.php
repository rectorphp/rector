<?php

declare (strict_types=1);
namespace Rector\Removing\ValueObject;

final class RemoveFuncCallArg
{
    /**
     * @readonly
     * @var string
     */
    private $function;
    /**
     * @readonly
     * @var int
     */
    private $argumentPosition;
    public function __construct(string $function, int $argumentPosition)
    {
        $this->function = $function;
        $this->argumentPosition = $argumentPosition;
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
