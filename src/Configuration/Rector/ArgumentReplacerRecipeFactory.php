<?php declare(strict_types=1);

namespace Rector\Configuration\Rector;

use Rector\Exception\Rector\InvalidRectorConfigurationException;
use Rector\Rector\Dynamic\ArgumentRector;

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
            $data['replacement_value'] ?? null,
            $data['replacement_map'] ?? []
        );
    }

    /**
     * @param mixed[] $data
     */
    private function validateArrayData(array $data): void
    {
        $this->ensureSingleReplacementStrategy($data);
        $this->ensureHasKey($data, 'class');
        $this->ensureHasKey($data, 'class');
        $this->ensureHasKey($data, 'method');
        $this->ensureHasKey($data, 'position');
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
            ArgumentRector::class,
            $key
        ));
    }

    /**
     * @param mixed[] $data
     */
    private function ensureSingleReplacementStrategy(array $data): void
    {
        if (array_key_exists('replacement_value', $data) && isset($data['replacement_map'])) {
            throw new InvalidRectorConfigurationException(sprintf(
                'Configuration for "%s" Rector should have single replacement strategy.',
                ArgumentRector::class
            ));
        }
    }
}
