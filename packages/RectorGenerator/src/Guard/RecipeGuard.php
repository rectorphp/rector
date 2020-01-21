<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Guard;

use Rector\RectorGenerator\Exception\ConfigurationException;

final class RecipeGuard
{
    /**
     * @var string[]
     */
    private const REQUIRED_KEYS = [
        'package',
        'name',
        'node_types',
        'code_before',
        'code_after',
        'description',
        'source',
        'set',
    ];

    /**
     * @param mixed[] $rectorRecipe
     */
    public function ensureRecipeIsValid(array $rectorRecipe): void
    {
        $requiredKeysCount = count(self::REQUIRED_KEYS);

        if (count(array_intersect(array_keys($rectorRecipe), self::REQUIRED_KEYS)) === $requiredKeysCount) {
            if (count($rectorRecipe['node_types']) < 1) {
                throw new ConfigurationException(sprintf(
                    '"%s" option requires at least one node, e.g. "FuncCall"',
                    'node_types'
                ));
            }

            return;
        }

        throw new ConfigurationException(sprintf(
            'Make sure "%s" keys are present in "parameters > rector_recipe"',
            implode('", "', self::REQUIRED_KEYS)
        ));
    }
}
