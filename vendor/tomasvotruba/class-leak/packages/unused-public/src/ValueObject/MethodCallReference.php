<?php

declare (strict_types=1);
namespace RectorPrefix202608\TomasVotruba\UnusedPublic\ValueObject;

final class MethodCallReference
{
    /**
     * @readonly
     */
    private string $class;
    /**
     * @readonly
     */
    private string $method;
    /**
     * @readonly
     */
    private bool $isLocal;
    public function __construct(string $class, string $method, bool $isLocal)
    {
        $this->class = $class;
        $this->method = $method;
        $this->isLocal = $isLocal;
    }
    public function getClass(): string
    {
        return $this->class;
    }
    public function getMethod(): string
    {
        return $this->method;
    }
    public function isLocal(): bool
    {
        return $this->isLocal;
    }
}
