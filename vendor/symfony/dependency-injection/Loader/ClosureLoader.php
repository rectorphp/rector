<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220501\Symfony\Component\DependencyInjection\Loader;

use RectorPrefix20220501\Symfony\Component\Config\Loader\Loader;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerBuilder;
/**
 * ClosureLoader loads service definitions from a PHP closure.
 *
 * The Closure has access to the container as its first argument.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ClosureLoader extends \RectorPrefix20220501\Symfony\Component\Config\Loader\Loader
{
    private $container;
    public function __construct(\RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerBuilder $container, string $env = null)
    {
        $this->container = $container;
        parent::__construct($env);
    }
    /**
     * {@inheritdoc}
     * @param mixed $resource
     * @return mixed
     */
    public function load($resource, string $type = null)
    {
        return $resource($this->container, $this->env);
    }
    /**
     * {@inheritdoc}
     * @param mixed $resource
     */
    public function supports($resource, string $type = null) : bool
    {
        return $resource instanceof \Closure;
    }
}
