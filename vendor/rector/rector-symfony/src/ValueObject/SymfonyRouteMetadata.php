<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject;

final class SymfonyRouteMetadata
{
    /**
     * @readonly
     */
    private string $name;
    /**
     * @readonly
     */
    private string $path;
    /**
     * @var array<string, mixed>
     * @readonly
     */
    private array $defaults;
    /**
     * @var array<string, mixed>
     * @readonly
     */
    private array $requirements;
    /**
     * @readonly
     */
    private string $host;
    /**
     * @var string[]
     * @readonly
     */
    private array $schemes;
    /**
     * @var string[]
     * @readonly
     */
    private array $methods;
    /**
     * @readonly
     */
    private string $condition;
    /**
     * @var array<string, mixed>
     * @readonly
     */
    private array $options;
    /**
     * Format <class>::<method>
     * @readonly
     */
    private ?string $controllerReference;
    /**
     * @param array<string, mixed> $defaults
     * @param array<string, mixed> $requirements
     * @param string[] $schemes
     * @param string[] $methods
     * @param array<string, mixed> $options
     */
    public function __construct(string $name, string $path, array $defaults, array $requirements, string $host, array $schemes, array $methods, string $condition, array $options)
    {
        $this->name = $name;
        $this->path = $path;
        $this->defaults = $defaults;
        $this->requirements = $requirements;
        $this->host = $host;
        $this->schemes = $schemes;
        $this->methods = $methods;
        $this->condition = $condition;
        $this->options = $options;
        $this->controllerReference = $defaults['_controller'] ?? null;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getPath() : string
    {
        return $this->path;
    }
    /**
     * @return array<string, mixed>
     */
    public function getDefaultsWithoutController() : array
    {
        $defaults = $this->defaults;
        unset($defaults['_controller']);
        return $defaults;
    }
    /**
     * @api used
     * @return mixed
     */
    public function getDefault(string $name)
    {
        return $this->defaults[$name] ?? null;
    }
    /**
     * @return array<string, mixed>
     */
    public function getRequirements() : array
    {
        return $this->requirements;
    }
    public function getHost() : string
    {
        return $this->host;
    }
    /**
     * @return string[]
     */
    public function getSchemes() : array
    {
        return $this->schemes;
    }
    /**
     * @return string[]
     */
    public function getMethods() : array
    {
        return $this->methods;
    }
    public function getCondition() : string
    {
        return $this->condition;
    }
    /**
     * @return array<string, mixed>
     */
    public function getOptionsWithoutDefaultCompilerClass() : array
    {
        $options = $this->options;
        $compilerClass = $options['compiler_class'] ?? null;
        if ($compilerClass === 'Symfony\\Component\\Routing\\RouteCompiler') {
            unset($options['compiler_class']);
        }
        return $options;
    }
    /**
     * Format <class>::<method>
     */
    public function getControllerReference() : ?string
    {
        return $this->controllerReference;
    }
}
