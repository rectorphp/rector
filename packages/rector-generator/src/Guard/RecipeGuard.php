<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Guard;

use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Configuration\Option;
use Rector\RectorGenerator\Exception\ConfigurationException;
use Rector\RectorGenerator\ValueObject\Configuration;
use Rector\RectorGenerator\ValueObject\RecipeOption;

final class RecipeGuard
{
    /**
     * @var string[]
     */
    private const REQUIRED_KEYS = [
        RecipeOption::PACKAGE,
        RecipeOption::NAME,
        RecipeOption::NODE_TYPES,
        RecipeOption::CODE_BEFORE,
        RecipeOption::CODE_AFTER,
        RecipeOption::DESCRIPTION,
        RecipeOption::SOURCE,
        RecipeOption::SET,
    ];

    /**
     * @param mixed[] $rectorRecipe
     */
    public function ensureRecipeIsValid(array $rectorRecipe, bool $isRectorRepository): void
    {
        $requiredKeysCount = count(self::REQUIRED_KEYS);

        if (count(array_intersect(array_keys($rectorRecipe), self::REQUIRED_KEYS)) !== $requiredKeysCount) {
            $message = sprintf(
                'Make sure "%s" keys are present in "parameters > %s"',
                Option::RECTOR_RECIPE,
                implode('", "', self::REQUIRED_KEYS)
            );
            throw new ConfigurationException($message);
        }

        if (count($rectorRecipe[RecipeOption::NODE_TYPES]) < 1) {
            $message = sprintf(
                '"%s" option requires at least one node, e.g. "%s"',
                FuncCall::class,
                RecipeOption::NODE_TYPES
            );
            throw new ConfigurationException($message);
        }

        $this->validatePackageOption($rectorRecipe[RecipeOption::PACKAGE], $isRectorRepository);
    }

    private function validatePackageOption(?string $package, bool $isRectorRepository): void
    {
        if ($package !== '' && $package !== null && $package !== Configuration::PACKAGE_UTILS) {
            return;
        }

        // only can be empty or utils when outside Rector repository
        if (! $isRectorRepository) {
            return;
        }

        $message = sprintf('Parameter "package" cannot be empty or "Utils", when in Rector repository');
        throw new ConfigurationException($message);
    }
}
