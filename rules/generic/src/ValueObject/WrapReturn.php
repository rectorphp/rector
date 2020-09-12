<?php

declare(strict_types=1);

namespace Rector\Generic\ValueObject;

final class WrapReturn
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $method;

    /**
     * @var bool
     */
    private $isArrayWrap = false;

    public function __construct(string $type, string $method, bool $isArrayWrap)
    {
        $this->type = $type;
        $this->method = $method;
        $this->isArrayWrap = $isArrayWrap;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function isArrayWrap(): bool
    {
        return $this->isArrayWrap;
    }
}
