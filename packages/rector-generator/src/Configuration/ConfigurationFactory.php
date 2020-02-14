<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Configuration;

use Nette\Utils\Strings;
use PhpParser\Node;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\RectorGenerator\ConfigResolver;
use Rector\RectorGenerator\Guard\RecipeGuard;
use Rector\RectorGenerator\Node\NodeClassProvider;
use Rector\RectorGenerator\ValueObject\Configuration;

final class ConfigurationFactory
{
    /**
     * @var NodeClassProvider
     */
    private $nodeClassProvider;

    /**
     * @var RecipeGuard
     */
    private $recipeGuard;

    /**
     * @var ConfigResolver
     */
    private $configResolver;

    public function __construct(
        NodeClassProvider $nodeClassProvider,
        RecipeGuard $recipeGuard,
        ConfigResolver $configResolver
    ) {
        $this->nodeClassProvider = $nodeClassProvider;
        $this->recipeGuard = $recipeGuard;
        $this->configResolver = $configResolver;
    }

    /**
     * @param mixed[] $rectorRecipe
     */
    public function createFromRectorRecipe(array $rectorRecipe): Configuration
    {
        $this->recipeGuard->ensureRecipeIsValid($rectorRecipe);

        $fqnNodeTypes = $this->resolveFullyQualifiedNodeTypes($rectorRecipe['node_types']);
        $category = $this->resolveCategoryFromFqnNodeTypes($fqnNodeTypes);

        return new Configuration(
            $rectorRecipe['package'],
            $rectorRecipe['name'],
            $category,
            $this->resolveFullyQualifiedNodeTypes($rectorRecipe['node_types']),
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
     * @param string[] $nodeTypes
     * @return string[]
     */
    private function resolveFullyQualifiedNodeTypes(array $nodeTypes): array
    {
        $nodeClasses = $this->nodeClassProvider->getNodeClasses();

        $fqnNodeTypes = [];
        foreach ($nodeTypes as $nodeType) {
            if ($nodeType === 'Node') {
                $fqnNodeTypes[] = Node::class;
                continue;
            }

            foreach ($nodeClasses as $nodeClass) {
                if ($this->isNodeClassMatch($nodeClass, $nodeType)) {
                    $fqnNodeTypes[] = $nodeClass;
                    continue 2;
                }
            }

            throw new ShouldNotHappenException(sprintf('Node endings with "%s" was not found', $nodeType));
        }

        return array_unique($fqnNodeTypes);
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

    private function isNodeClassMatch(string $nodeClass, string $nodeType): bool
    {
        // skip "magic", they're used less than rarely
        if (Strings::contains($nodeClass, 'MagicConst')) {
            return false;
        }

        if (Strings::endsWith($nodeClass, '\\' . $nodeType)) {
            return true;
        }

        // in case of forgotten _
        return Strings::endsWith($nodeClass, '\\' . $nodeType . '_');
    }
}
