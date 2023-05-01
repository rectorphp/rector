<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202305\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

use RectorPrefix202305\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use RectorPrefix202305\Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use RectorPrefix202305\Symfony\Component\ExpressionLanguage\Expression;
trait FactoryTrait
{
    /**
     * Sets a factory.
     *
     * @return $this
     * @param string|mixed[]|\Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator|\Symfony\Component\ExpressionLanguage\Expression $factory
     */
    public final function factory($factory)
    {
        if (\is_string($factory) && 1 === \substr_count($factory, ':')) {
            $factoryParts = \explode(':', $factory);
            throw new InvalidArgumentException(\sprintf('Invalid factory "%s": the "service:method" notation is not available when using PHP-based DI configuration. Use "[service(\'%s\'), \'%s\']" instead.', $factory, $factoryParts[0], $factoryParts[1]));
        }
        if ($factory instanceof Expression) {
            $factory = '@=' . $factory;
        }
        $this->definition->setFactory(static::processValue($factory, \true));
        return $this;
    }
}
