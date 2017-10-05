<?php declare(strict_types=1);

namespace Rector\Configuration\Normalizer;

use Rector\Exception\Configuration\InvalidConfigurationTypeException;

final class RectorClassNormalizer
{
    /**
     * @param mixed[] $rectors
     * @return mixed[]
     */
    public function normalizer(array $rectors): array
    {
        $configuredClasses = [];
        foreach ($rectors as $name => $class) {
            if ($class === null) { // rector with commented configuration
                $config = [];
            } elseif (is_array($class)) { // rector with configuration
                $config = $class;
            } elseif (! is_string($name)) { // only rector item
                $name = $class;
                $config = [];
            } else {
                $config = $class;

                throw new InvalidConfigurationTypeException(sprintf(
                    'Configuration of "%s" rector has to be array; "%s" given with "%s".',
                    $name,
                    gettype($config),
                    $config
                ));
            }

            $configuredClasses[$name] = $config;
        }

        return $configuredClasses;
    }
}
