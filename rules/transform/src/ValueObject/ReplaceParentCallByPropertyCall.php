<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

final class ReplaceParentCallByPropertyCall
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
    private $property;

    public function __construct(string $class, string $method, string $property)
    {
        $this->class = $class;
        $this->method = $method;
        $this->property = $property;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getProperty(): string
    {
        return $this->property;
    }
}
