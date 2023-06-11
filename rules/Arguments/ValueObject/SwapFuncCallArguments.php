<?php

declare (strict_types=1);
namespace Rector\Arguments\ValueObject;

final class SwapFuncCallArguments
{
    /**
     * @readonly
     * @var string
     */
    private $function;
    /**
     * @var array<int, int>
     * @readonly
     */
    private $order;
    /**
     * @param array<int, int> $order
     */
    public function __construct(string $function, array $order)
    {
        $this->function = $function;
        $this->order = $order;
    }
    public function getFunction() : string
    {
        return $this->function;
    }
    /**
     * @return array<int, int>
     */
    public function getOrder() : array
    {
        return $this->order;
    }
}
