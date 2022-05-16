<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject;

class SymfonyRouteMetadata
{
    /**
     * @readonly
     * @var string
     */
    private $name;
    /**
     * @readonly
     * @var string
     */
    private $path;
    /**
     * @var mixed[]
     * @readonly
     */
    private $defaults = [];
    /**
     * @var string[]
     * @readonly
     */
    private $requirements = [];
    /**
     * @readonly
     * @var string
     */
    private $host = '';
    /**
     * @var string[]
     * @readonly
     */
    private $schemes = [];
    /**
     * @var string[]
     * @readonly
     */
    private $methods = [];
    /**
     * @readonly
     * @var string
     */
    private $condition = '';
    /**
     * @param mixed[]  $defaults
     * @param string[]  $requirements
     * @param string[]  $schemes
     * @param string[]  $methods
     */
    public function __construct(string $name, string $path, array $defaults = [], array $requirements = [], string $host = '', array $schemes = [], array $methods = [], string $condition = '')
    {
        $this->name = $name;
        $this->path = $path;
        $this->defaults = $defaults;
        $this->requirements = $requirements;
        $this->host = $host;
        $this->schemes = $schemes;
        $this->methods = $methods;
        $this->condition = $condition;
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
     * @return mixed[]
     */
    public function getDefaults() : array
    {
        return $this->defaults;
    }
    /**
     * @return mixed
     */
    public function getDefault(string $name)
    {
        return $this->defaults[$name] ?? null;
    }
    /**
     * @return string[]
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
}
