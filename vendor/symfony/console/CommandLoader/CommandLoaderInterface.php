<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211209\Symfony\Component\Console\CommandLoader;

use RectorPrefix20211209\Symfony\Component\Console\Command\Command;
use RectorPrefix20211209\Symfony\Component\Console\Exception\CommandNotFoundException;
/**
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
interface CommandLoaderInterface
{
    /**
     * Loads a command.
     *
     * @throws CommandNotFoundException
     * @param string $name
     */
    public function get($name) : \RectorPrefix20211209\Symfony\Component\Console\Command\Command;
    /**
     * Checks if a command exists.
     * @param string $name
     */
    public function has($name) : bool;
    /**
     * @return string[]
     */
    public function getNames() : array;
}
