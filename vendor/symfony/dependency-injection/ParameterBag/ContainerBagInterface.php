<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202208\Symfony\Component\DependencyInjection\ParameterBag;

use RectorPrefix202208\Psr\Container\ContainerInterface;
use RectorPrefix202208\Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
/**
 * ContainerBagInterface is the interface implemented by objects that manage service container parameters.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
interface ContainerBagInterface extends ContainerInterface
{
    /**
     * Gets the service container parameters.
     */
    public function all() : array;
    /**
     * Replaces parameter placeholders (%name%) by their values.
     *
     * @throws ParameterNotFoundException if a placeholder references a parameter that does not exist
     * @param mixed $value
     */
    public function resolveValue($value);
    /**
     * Escape parameter placeholders %.
     * @param mixed $value
     * @return mixed
     */
    public function escapeValue($value);
    /**
     * Unescape parameter placeholders %.
     * @param mixed $value
     * @return mixed
     */
    public function unescapeValue($value);
}
