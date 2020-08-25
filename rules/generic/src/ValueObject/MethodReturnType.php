<?php

declare(strict_types=1);

namespace Rector\Generic\ValueObject;

final class MethodReturnType
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
     * @var string
     */
    private $type;

    public function __construct(string $class, string $method, string $type)
    {
        $this->class = $class;
        $this->method = $method;
        $this->type = $type;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
