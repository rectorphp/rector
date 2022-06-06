<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Transform\ValueObject;

use RectorPrefix20220606\Rector\Core\Validation\RectorAssert;
final class StringToClassConstant
{
    /**
     * @readonly
     * @var string
     */
    private $string;
    /**
     * @readonly
     * @var string
     */
    private $class;
    /**
     * @readonly
     * @var string
     */
    private $constant;
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
