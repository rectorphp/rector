<?php

declare (strict_types=1);
namespace Rector\Arguments\ValueObject;

final class SwapFuncCallArguments
{
    /**
     * @var string
     */
    private $function;
    /**
     * @var int[]
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
