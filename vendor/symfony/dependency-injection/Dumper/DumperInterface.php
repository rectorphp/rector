<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\DependencyInjection\Dumper;

/**
 * DumperInterface is the interface implemented by service container dumper classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface DumperInterface
{
    /**
     * Dumps the service container.
     *
     * @return string|array The representation of the service container
     * @param mixed[] $options
     */
    public function dump($options = []);
}
