<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202306\Symfony\Component\DependencyInjection\Argument;

use RectorPrefix202306\Symfony\Component\DependencyInjection\ServiceLocator as BaseServiceLocator;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 *
 * @internal
 */
class ServiceLocator extends BaseServiceLocator
{
    /**
     * @var \Closure
     */
    private $factory;
    /**
     * @var mixed[]
     */
    private $serviceMap;
    /**
     * @var mixed[]|null
     */
    private $serviceTypes;
    public function __construct(\Closure $factory, array $serviceMap, array $serviceTypes = null)
    {
        $this->factory = $factory;
        $this->serviceMap = $serviceMap;
        $this->serviceTypes = $serviceTypes;
        parent::__construct($serviceMap);
    }
    /**
     * {@inheritdoc}
     * @return mixed
     */
    public function get(string $id)
    {
        return \count($this->serviceMap[$id] ?? []) === 0 ? parent::get($id) : (\count($this->serviceMap[$id] ?? []) === 1 ? $this->serviceMap[$id][0] : ($this->factory)(...$this->serviceMap[$id]));
    }
    /**
     * {@inheritdoc}
     */
    public function getProvidedServices() : array
    {
        return $this->serviceTypes = $this->serviceTypes ?? \array_map(function () {
            return '?';
        }, $this->serviceMap);
    }
}
