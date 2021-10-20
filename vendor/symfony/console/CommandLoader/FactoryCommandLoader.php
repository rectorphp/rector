<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\Console\CommandLoader;

use RectorPrefix20211020\Symfony\Component\Console\Exception\CommandNotFoundException;
/**
 * A simple command loader using factories to instantiate commands lazily.
 *
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 */
class FactoryCommandLoader implements \RectorPrefix20211020\Symfony\Component\Console\CommandLoader\CommandLoaderInterface
{
    private $factories;
    /**
     * @param callable[] $factories Indexed by command names
     */
    public function __construct(array $factories)
    {
        $this->factories = $factories;
    }
    /**
     * {@inheritdoc}
     * @param string $name
     */
    public function has($name)
    {
        return isset($this->factories[$name]);
    }
    /**
     * {@inheritdoc}
     * @param string $name
     */
    public function get($name)
    {
        if (!isset($this->factories[$name])) {
            throw new \RectorPrefix20211020\Symfony\Component\Console\Exception\CommandNotFoundException(\sprintf('Command "%s" does not exist.', $name));
        }
        $factory = $this->factories[$name];
        return $factory();
    }
    /**
     * {@inheritdoc}
     */
    public function getNames()
    {
        return \array_keys($this->factories);
    }
}
