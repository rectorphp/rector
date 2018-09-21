<?php declare(strict_types=1);

namespace Rector\Configuration\Rector\Guard;

use Rector\Exception\Rector\InvalidRectorConfigurationException;
use function Safe\sprintf;

final class RecipeGuard
{
    /**
     * @param mixed[] $data
     * @param string[] $keys
     */
    public static function ensureHasKeys(array $data, array $keys, string $class): void
    {
        $missingKeys = array_diff_key(array_flip($keys), $data);

        if (count($missingKeys) === 0) {
            return;
        }

        throw new InvalidRectorConfigurationException(sprintf(
            'Configuration for "%s" is missing keys: "%s". Complete them to YAML config.',
            $class,
            implode('", "', $missingKeys)
        ));
    }
}
