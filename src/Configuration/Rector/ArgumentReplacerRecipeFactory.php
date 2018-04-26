<?php declare(strict_types=1);

namespace Rector\Configuration\Rector;

use Rector\Exception\Rector\InvalidRectorConfigurationException;
use Rector\Rector\Dynamic\ArgumentReplacerRector;

final class ArgumentReplacerRecipeFactory
{
    /**
     * @param mixed[] $data
     */
    public function createFromArray(array $data): ArgumentReplacerRecipe
    {
        $this->validateArrayData($data);

        return new ArgumentReplacerRecipe(
            $data['class'],
            $data['method'],
            $data['position'],
            $data['type'],
            $data['default_value'] ?? null,
            $data['replace_map'] ?? []
        );
    }

    /**
     * @param mixed[] $data
     */
    private function validateArrayData(array $data): void
    {
        $this->ensureHasKey($data, 'class');
        $this->ensureHasKey($data, 'class');
        $this->ensureHasKey($data, 'method');
        $this->ensureHasKey($data, 'position');
        $this->ensureHasKey($data, 'type');

        if ($data['type'] === ArgumentReplacerRecipe::TYPE_REPLACED_DEFAULT_VALUE) {
            self::ensureHasKey($data, 'replace_map');
        }
    }

    /**
     * @param mixed[] $data
     */
    private function ensureHasKey(array $data, string $key): void
    {
        if (isset($data[$key])) {
            return;
        }

        throw new InvalidRectorConfigurationException(sprintf(
            'Configuration for "%s" Rector should have "%s" key, but is missing.',
            ArgumentReplacerRector::class,
            $key
        ));
    }
}
