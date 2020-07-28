<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Configuration;

use Nette\Utils\Strings;
use Rector\Core\Set\SetResolver;
use Rector\RectorGenerator\Guard\RecipeGuard;
use Rector\RectorGenerator\ValueObject\Configuration;
use Rector\RectorGenerator\ValueObject\RecipeOption;

final class ConfigurationFactory
{
    /**
     * @var RecipeGuard
     */
    private $recipeGuard;

    /**
     * @var SetResolver
     */
    private $setResolver;

    public function __construct(RecipeGuard $recipeGuard, SetResolver $setResolver)
    {
        $this->recipeGuard = $recipeGuard;
        $this->setResolver = $setResolver;
    }

    /**
     * @param mixed[] $rectorRecipe
     */
    public function createFromRectorRecipe(array $rectorRecipe): Configuration
    {
        $this->recipeGuard->ensureRecipeIsValid($rectorRecipe);

        $nodeTypeClasses = $rectorRecipe[RecipeOption::NODE_TYPES];

        $category = $this->resolveCategoryFromFqnNodeTypes($nodeTypeClasses);

        $extraFileContent = isset($rectorRecipe[RecipeOption::EXTRA_FILE_CONTENT]) ? $this->normalizeCode(
            $rectorRecipe[RecipeOption::EXTRA_FILE_CONTENT]
        ) : null;

        $set = $rectorRecipe['set'] ? $this->setResolver->resolveSetByName($rectorRecipe['set']) : null;

        return new Configuration(
            $rectorRecipe[RecipeOption::PACKAGE],
            $rectorRecipe[RecipeOption::NAME],
            $category,
            $nodeTypeClasses,
            $rectorRecipe[RecipeOption::DESCRIPTION],
            $this->normalizeCode($rectorRecipe[RecipeOption::CODE_BEFORE]),
            $this->normalizeCode($rectorRecipe[RecipeOption::CODE_AFTER]),
            $extraFileContent,
            $rectorRecipe[RecipeOption::EXTRA_FILE_NAME] ?? null,
            (array) $rectorRecipe[RecipeOption::CONFIGURATION],
            array_filter((array) $rectorRecipe[RecipeOption::SOURCE]),
            $set,
            $this->isPhpSnippet($rectorRecipe[RecipeOption::CODE_BEFORE])
        );
    }

    /**
     * @param class-string[] $nodeTypes
     */
    private function resolveCategoryFromFqnNodeTypes(array $nodeTypes): string
    {
        return (string) Strings::after($nodeTypes[0], '\\', -1);
    }

    private function normalizeCode(string $code): string
    {
        if (Strings::startsWith($code, '<?php')) {
            $code = ltrim($code, '<?php');
        }

        return trim($code);
    }

    private function isPhpSnippet(string $code): bool
    {
        return Strings::startsWith($code, '<?php');
    }
}
