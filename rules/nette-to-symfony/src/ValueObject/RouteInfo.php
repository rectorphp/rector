<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\ValueObject;

final class RouteInfo
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
    private $path;

    /**
     * @var string[]
     */
    private $httpMethods = [];

    /**
     * @param string[] $httpMethods
     */
    public function __construct(string $class, string $method, string $path, array $httpMethods = [])
    {
        $this->class = $class;
        $this->method = $method;
        $this->path = $path;
        $this->httpMethods = $httpMethods;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string[]
     */
    public function getHttpMethods(): array
    {
        return $this->httpMethods;
    }
}
