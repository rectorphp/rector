<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use Rector\Validation\RectorAssert;
final class StringToClassConstant
{
    /**
     * @readonly
     */
    private string $string;
    /**
     * @readonly
     */
    private string $class;
    /**
     * @readonly
     */
    private string $constant;
    public function __construct(string $string, string $class, string $constant)
    {
        $this->string = $string;
        $this->class = $class;
        $this->constant = $constant;
        RectorAssert::className($class);
    }
    public function getString() : string
    {
        return $this->string;
    }
    public function getClass() : string
    {
        return $this->class;
    }
    public function getConstant() : string
    {
        return $this->constant;
    }
}
