<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Symplify\PackageBuilder\Parameter;

use RectorPrefix20211020\Symfony\Component\DependencyInjection\Container;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
/**
 * @api
 * @see \Symplify\PackageBuilder\Tests\Parameter\ParameterProviderTest
 */
final class ParameterProvider
{
    /**
     * @var array<string, mixed>
     */
    private $parameters = [];
    /**
     * @param Container|ContainerInterface $container
     */
    public function __construct(\RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $parameterBag = $container->getParameterBag();
        $this->parameters = $parameterBag->all();
    }
    public function hasParameter(string $name) : bool
    {
        return isset($this->parameters[$name]);
    }
    /**
     * @api
     * @return mixed|null
     */
    public function provideParameter(string $name)
    {
        return $this->parameters[$name] ?? null;
    }
    /**
     * @api
     */
    public function provideStringParameter(string $name) : string
    {
        $this->ensureParameterIsSet($name);
        return (string) $this->parameters[$name];
    }
    /**
     * @api
     * @return mixed[]
     */
    public function provideArrayParameter(string $name) : array
    {
        $this->ensureParameterIsSet($name);
        return $this->parameters[$name];
    }
    /**
     * @api
     */
    public function provideBoolParameter(string $parameterName) : bool
    {
        return $this->parameters[$parameterName] ?? \false;
    }
    public function changeParameter(string $name, $value) : void
    {
        $this->parameters[$name] = $value;
    }
    /**
     * @api
     * @return mixed[]
     */
    public function provide() : array
    {
        return $this->parameters;
    }
    /**
     * @api
     */
    public function provideIntParameter(string $name) : int
    {
        $this->ensureParameterIsSet($name);
        return (int) $this->parameters[$name];
    }
    /**
     * @api
     */
    public function ensureParameterIsSet(string $name) : void
    {
        if (\array_key_exists($name, $this->parameters)) {
            return;
        }
        throw new \RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException($name);
    }
}
