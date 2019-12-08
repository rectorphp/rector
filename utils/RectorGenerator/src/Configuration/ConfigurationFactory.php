<?php

declare(strict_types=1);

namespace Rector\Utils\RectorGenerator\Configuration;

use Nette\Utils\Strings;
use PhpParser\Node;
use Rector\Exception\ShouldNotHappenException;
use Rector\Set\Set;
use Rector\Utils\RectorGenerator\Guard\RecipeGuard;
use Rector\Utils\RectorGenerator\Node\NodeClassProvider;
use Rector\Utils\RectorGenerator\ValueObject\Configuration;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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

    public function __construct(NodeClassProvider $nodeClassProvider, RecipeGuard $recipeGuard)
    {
        $this->nodeClassProvider = $nodeClassProvider;
        $this->recipeGuard = $recipeGuard;
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
            trim(ltrim($rectorRecipe['code_before'], '<?php')),
            trim(ltrim($rectorRecipe['code_after'], '<?php')),
            array_filter((array) $rectorRecipe['source']),
            $this->resolveSetConfig($rectorRecipe['set'])
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

    private function resolveSetConfig(string $set): ?string
    {
        if ($set === '') {
            return null;
        }

        $fileSet = sprintf('#^%s(\.yaml)?$#', $set);
        $finder = Finder::create()->files()
            ->in(Set::SET_DIRECTORY)
            ->name($fileSet);

        /** @var SplFileInfo[] $fileInfos */
        $fileInfos = iterator_to_array($finder->getIterator());
        if (count($fileInfos) === 0) {
            // assume new one is created
            $match = Strings::match($set, '#\/(?<name>[a-zA-Z_-]+])#');
            if (isset($match['name'])) {
                return Set::SET_DIRECTORY . '/' . $match['name'] . '/' . $set;
            }

            return null;
        }

        /** @var SplFileInfo $foundSetConfigFileInfo */
        $foundSetConfigFileInfo = array_pop($fileInfos);

        return $foundSetConfigFileInfo->getRealPath();
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
