<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202208\Symfony\Component\DependencyInjection;

use RectorPrefix202208\Symfony\Component\DependencyInjection\Exception\RuntimeException;
/**
 * The EnvVarProcessorInterface is implemented by objects that manage environment-like variables.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
interface EnvVarProcessorInterface
{
    /**
     * Returns the value of the given variable as managed by the current instance.
     *
     * @param string   $prefix The namespace of the variable
     * @param string   $name   The name of the variable within the namespace
     * @param \Closure $getEnv A closure that allows fetching more env vars
     *
     * @throws RuntimeException on error
     * @return mixed
     */
    public function getEnv(string $prefix, string $name, \Closure $getEnv);
    /**
     * @return string[] The PHP-types managed by getEnv(), keyed by prefixes
     */
    public static function getProvidedTypes() : array;
}
