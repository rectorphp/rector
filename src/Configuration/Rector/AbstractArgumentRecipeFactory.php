<?php declare(strict_types=1);

namespace Rector\Configuration\Rector;

use Rector\Exception\Rector\InvalidRectorConfigurationException;
use Rector\Rector\Dynamic\AbstractArgumentRector;

abstract class AbstractArgumentRecipeFactory
{
    /**
     * @param mixed[] $data
     */
    protected function ensureHasKey(array $data, string $key): void
    {
        if (isset($data[$key])) {
            return;
        }

        throw new InvalidRectorConfigurationException(sprintf(
            'Configuration for "%s" Rector should have "%s" key, but is missing.',
            get_called_class(),
            $key
        ));
    }

    /**
     * @param mixed[] $data
     */
    protected function validateArrayData(array $data): void
    {
        $this->ensureHasKey($data, 'class');
        $this->ensureHasKey($data, 'method');
        $this->ensureHasKey($data, 'position');
    }
}
