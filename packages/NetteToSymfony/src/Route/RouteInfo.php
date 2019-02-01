<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Route;

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
     * @var string|null
     */
    private $name;

    /**
     * @var string[]
     */
    private $httpMethods = [];

    /**
     * @param string[] $httpMethods
     */
    public function __construct(string $class, string $method, string $path, ?string $name = null, array $httpMethods = [])
    {
        $this->class = $class;
        $this->method = $method;
        $this->path = $path;
        $this->name = $name;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getHttpMethods(): array
    {
        return $this->httpMethods;
    }
}
