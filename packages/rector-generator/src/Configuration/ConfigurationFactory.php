<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Configuration;

use Nette\Utils\Strings;
use Rector\RectorGenerator\ConfigResolver;
use Rector\RectorGenerator\Guard\RecipeGuard;
use Rector\RectorGenerator\ValueObject\Configuration;

final class ConfigurationFactory
{
    /**
     * @var RecipeGuard
     */
    private $recipeGuard;

    /**
     * @var ConfigResolver
     */
    private $configResolver;

    public function __construct(RecipeGuard $recipeGuard, ConfigResolver $configResolver)
    {
        $this->recipeGuard = $recipeGuard;
        $this->configResolver = $configResolver;
    }

    /**
     * @param mixed[] $rectorRecipe
     */
    public function createFromRectorRecipe(array $rectorRecipe): Configuration
    {
        $this->recipeGuard->ensureRecipeIsValid($rectorRecipe);

        $nodeTypeClasses = $rectorRecipe['node_types'];
        $category = $this->resolveCategoryFromFqnNodeTypes($nodeTypeClasses);

        return new Configuration(
            $rectorRecipe['package'],
            $rectorRecipe['name'],
            $category,
            $nodeTypeClasses,
            $rectorRecipe['description'],
            $this->normalizeCode($rectorRecipe['code_before']),
            $this->normalizeCode($rectorRecipe['code_after']),
            isset($rectorRecipe['extra_file_content']) ? $this->normalizeCode(
                $rectorRecipe['extra_file_content']
            ) : null,
            $rectorRecipe['extra_file_name'] ?? null,
            array_filter((array) $rectorRecipe['source']),
            $this->configResolver->resolveSetConfig($rectorRecipe['set']),
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
