<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

final class StringToClassConstant
{
    /**
     * @var string
     */
    private $string;
    /**
     * @var string
     */
    private $class;
    /**
     * @var string
     */
    private $constant;
    public function __construct(string $string, string $class, string $constant)
    {
        $this->string = $string;
        $this->class = $class;
        $this->constant = $constant;
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
