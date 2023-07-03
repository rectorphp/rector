<?php

declare (strict_types=1);
namespace Rector\Core\Configuration\Parameter;

use Rector\Core\Configuration\Option;
use RectorPrefix202307\Symfony\Component\DependencyInjection\Container;
use RectorPrefix202307\Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use RectorPrefix202307\Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
/**
 * @deprecated Use SimpleParameterProvider to avoid coupling with Symfony container.
 * This class will be removed in next major release
 *
 * @api
 */
final class ParameterProvider
{
    /**
     * @var array<string, mixed>
     */
    private $parameters = [];
    public function __construct(Container $container)
    {
        /** @var ParameterBagInterface $parameterBag */
        $parameterBag = $container->getParameterBag();
        $this->parameters = $parameterBag->all();
    }
    /**
     * @param Option::* $name
     */
    public function hasParameter(string $name) : bool
    {
        return isset($this->parameters[$name]);
    }
    /**
     * @param Option::* $name
     * @api
     * @return mixed
     */
    public function provideParameter(string $name)
    {
        return $this->parameters[$name] ?? null;
    }
    /**
     * @param Option::* $name
     * @return mixed[]
     */
    public function provideArrayParameter(string $name) : array
    {
        $this->ensureParameterIsSet($name);
        return $this->parameters[$name];
    }
    /**
     * @param Option::* $name
     * @api
     */
    public function provideBoolParameter(string $name) : bool
    {
        return $this->parameters[$name] ?? \false;
    }
    /**
     * @param Option::* $name
     * @param mixed $value
     */
    public function changeParameter(string $name, $value) : void
    {
        $this->parameters[$name] = $value;
        \Rector\Core\Configuration\Parameter\SimpleParameterProvider::setParameter($name, $value);
    }
    /**
     * @api
     */
    public function ensureParameterIsSet(string $name) : void
    {
        if (\array_key_exists($name, $this->parameters)) {
            return;
        }
        throw new ParameterNotFoundException($name);
    }
}
