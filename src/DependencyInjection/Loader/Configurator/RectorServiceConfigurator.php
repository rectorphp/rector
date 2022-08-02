<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection\Loader\Configurator;

use Rector\Core\Configuration\ValueObjectInliner;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use RectorPrefix202208\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use RectorPrefix202208\Symfony\Component\DependencyInjection\Loader\Configurator\ServiceConfigurator;
/**
 * @api
 * Same as Symfony service configurator, with extra "configure()" method for easier DX
 */
final class RectorServiceConfigurator extends ServiceConfigurator
{
    /**
     * @deprecated Use @see \Rector\Config\RectorConfig instead
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : self
    {
        $this->ensureClassIsConfigurable($this->id);
        // decorate with value object inliner so Symfony understands, see https://getrector.org/blog/2020/09/07/how-to-inline-value-object-in-symfony-php-config
        \array_walk_recursive($configuration, static function (&$value) {
            if (\is_object($value)) {
                $value = ValueObjectInliner::inline($value);
            }
            return $value;
        });
        $this->call('configure', [$configuration]);
        return $this;
    }
    private function ensureClassIsConfigurable(?string $class) : void
    {
        if ($class === null) {
            throw new InvalidConfigurationException('The class is missing');
        }
        if (!\is_a($class, ConfigurableRectorInterface::class, \true)) {
            $errorMessage = \sprintf('The service "%s" is not configurable. Make it implement "%s" or remove "configure()" call.', $class, ConfigurableRectorInterface::class);
            throw new InvalidConfigurationException($errorMessage);
        }
    }
}
