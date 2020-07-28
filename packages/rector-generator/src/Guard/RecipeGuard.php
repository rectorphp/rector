<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Guard;

use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Configuration\Option;
use Rector\RectorGenerator\Exception\ConfigurationException;
use Rector\RectorGenerator\ValueObject\RecipeOption;

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
            if (count($rectorRecipe[RecipeOption::NODE_TYPES]) < 1) {
                throw new ConfigurationException(sprintf(
                    '"%s" option requires at least one node, e.g. "%s"',
                    FuncCall::class,
                    RecipeOption::NODE_TYPES
                ));
            }

            return;
        }

        $message = sprintf(
            'Make sure "%s" keys are present in "parameters > %s"',
            Option::RECTOR_RECIPE,
            implode('", "', self::REQUIRED_KEYS)
        );
        throw new ConfigurationException($message);
    }
}
