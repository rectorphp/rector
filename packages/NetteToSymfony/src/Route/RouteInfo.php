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
    private $methods = [];

    /**
     * @param string[] $methods
     */
    public function __construct(string $class, string $method, string $path, ?string $name = null, array $methods = [])
    {
        $this->class = $class;
        $this->method = $method;
        $this->path = $path;
        $this->name = $name;
        $this->methods = $methods;
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
    public function getMethods(): array
    {
        return $this->methods;
    }
}
