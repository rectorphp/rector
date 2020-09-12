<?php

declare(strict_types=1);

namespace Rector\Generic\ValueObject;

final class SwapClassMethodArguments
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array<int, int>
     */
    private $order = [];

    /**
     * @param array<int, int> $order
     */
    public function __construct(string $class, string $method, array $order)
    {
        $this->class = $class;
        $this->method = $method;
        $this->order = $order;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array<int, int>
     */
    public function getOrder(): array
    {
        return $this->order;
    }
}
