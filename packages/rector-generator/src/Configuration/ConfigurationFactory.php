<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Configuration;

use Nette\Utils\Strings;
use Rector\Core\Set\SetResolver;
use Rector\RectorGenerator\Guard\RecipeGuard;
use Rector\RectorGenerator\ValueObject\Configuration;

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

        $nodeTypeClasses = $rectorRecipe['node_types'];
        $category = $this->resolveCategoryFromFqnNodeTypes($nodeTypeClasses);
        $extraFileContent = isset($rectorRecipe['extra_file_content']) ? $this->normalizeCode(
            $rectorRecipe['extra_file_content']
        ) : null;
        $set = $this->setResolver->resolveSetByName($rectorRecipe['set']);

        return new Configuration(
            $rectorRecipe['package'],
            $rectorRecipe['name'],
            $category,
            $nodeTypeClasses,
            $rectorRecipe['description'],
            $this->normalizeCode($rectorRecipe['code_before']),
            $this->normalizeCode($rectorRecipe['code_after']),
            $extraFileContent,
            $rectorRecipe['extra_file_name'] ?? null,
            array_filter((array) $rectorRecipe['source']),
            $set,
            $this->detectPhpSnippet($rectorRecipe['code_before'])
        );
    }

    /**
     * @param string[] $fqnNodeTypes
     */
    private function resolveCategoryFromFqnNodeTypes(array $fqnNodeTypes): string
    {
        return (string) Strings::after($fqnNodeTypes[0], '\\', -1);
    }

    private function normalizeCode(string $code): string
    {
        if (Strings::startsWith($code, '<?php')) {
            $code = ltrim($code, '<?php');
        }

        return trim($code);
    }

    private function detectPhpSnippet(string $code): bool
    {
        return Strings::startsWith($code, '<?php');
    }
}
