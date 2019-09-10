<?php declare(strict_types=1);

namespace Rector\PSR4\Rector\Namespace_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PSR4\Collector\RenamedClassesCollector;
use Rector\PSR4\Composer\PSR4AutoloadPathsProvider;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\RectorDefinition;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 */
final class NormalizeNamespaceByPSR4ComposerAutoloadRector extends AbstractRector
{
    /**
     * @var PSR4AutoloadPathsProvider
     */
    private $psR4AutoloadPathsProvider;

    /**
     * @var RenamedClassesCollector
     */
    private $renamedClassesCollector;

    public function __construct(
        PSR4AutoloadPathsProvider $psR4AutoloadPathsProvider,
        RenamedClassesCollector $renamedClassesCollector
    ) {
        $this->psR4AutoloadPathsProvider = $psR4AutoloadPathsProvider;
        $this->renamedClassesCollector = $renamedClassesCollector;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes namespace and class names to match PSR-4 in composer.json autoload section'
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Namespace_::class];
    }

    /**
     * @param Namespace_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $expectedNamespace = $this->getExpectedNamespace($node);
        if ($expectedNamespace === null) {
            return null;
        }

        $currentNamespace = $this->getName($node);

        // namespace is correct → skip
        if ($currentNamespace === $expectedNamespace) {
            return null;
        }

        // change it
        $node->name = new Name($expectedNamespace);

        // add use import for classes from the same namespace
        $newUseImports = [];
        $this->traverseNodesWithCallable($node, function (Node $node) use ($currentNamespace, &$newUseImports) {
            if (! $node instanceof Name) {
                return null;
            }

            /** @var Name|null $originalName */
            $originalName = $node->getAttribute('originalName');
            if ($originalName instanceof Name) {
                if ($currentNamespace . '\\' . $originalName->toString() === $this->getName($node)) {
                    // this needs to be imported
                    $newUseImports[] = $this->getName($node);
                }
            }
        });

        $newUseImports = array_unique($newUseImports);

        if ($newUseImports) {
            $useImports = $this->createUses($newUseImports);
            $node->stmts = array_merge($useImports, $node->stmts);
        }

        /** @var SmartFileInfo $smartFileInfo */
        $smartFileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        $oldClassName = $currentNamespace . '\\' . $smartFileInfo->getBasenameWithoutSuffix();
        $newClassName = $expectedNamespace . '\\' . $smartFileInfo->getBasenameWithoutSuffix();

        $this->renamedClassesCollector->addClassRename($oldClassName, $newClassName);

        // collect changed class
        return $node;
    }

    private function getExpectedNamespace(Node $node): ?string
    {
        /** @var SmartFileInfo $smartFileInfo */
        $smartFileInfo = $node->getAttribute(AttributeKey::FILE_INFO);

        $psr4Autoloads = $this->psR4AutoloadPathsProvider->provide();

        foreach ($psr4Autoloads as $namespace => $path) {
            if (Strings::startsWith($smartFileInfo->getRelativeDirectoryPath(), $path)) {
                $expectedNamespace = $namespace . $this->resolveExtraNamespace($smartFileInfo, $path);

                return rtrim($expectedNamespace, '\\');
            }
        }

        return null;
    }

    /**
     * Get the extra path that is not included in root PSR-4 namespace
     */
    private function resolveExtraNamespace(SmartFileInfo $smartFileInfo, string $path): string
    {
        $extraNamespace = Strings::substring($smartFileInfo->getRelativeDirectoryPath(), Strings::length($path) + 1);
        $extraNamespace = Strings::replace($extraNamespace, '#/#', '\\');

        return trim($extraNamespace);
    }

    /**
     * @param string[] $newUseImports
     * @return Use_[]
     */
    private function createUses(array $newUseImports): array
    {
        $uses = [];

        foreach ($newUseImports as $newUseImport) {
            $useUse = new UseUse(new Name($newUseImport));
            $uses[] = new Use_([$useUse]);
        }

        return $uses;
    }
}
