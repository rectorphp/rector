<?php

declare(strict_types=1);

namespace Rector\FileSystemRector\ValueObjectFactory;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Autodiscovery\Configuration\CategoryNamespaceProvider;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\FileSystemRector\ValueObject\AddedFileWithNodes;
use Rector\PSR4\Collector\RenamedClassesCollector;
use Rector\PSR4\FileInfoAnalyzer\FileInfoDeletionAnalyzer;
use Rector\PSR4\FileRelocationResolver;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddedFileWithNodesFactory
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var FileRelocationResolver
     */
    private $fileRelocationResolver;

    /**
     * @var CategoryNamespaceProvider
     */
    private $categoryNamespaceProvider;

    /**
     * @var RenamedClassesCollector
     */
    private $renamedClassesCollector;

    /**
     * @var FileInfoDeletionAnalyzer
     */
    private $fileInfoDeletionAnalyzer;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        CategoryNamespaceProvider $categoryNamespaceProvider,
        FileRelocationResolver $fileRelocationResolver,
        RenamedClassesCollector $renamedClassesCollector,
        FileInfoDeletionAnalyzer $fileInfoDeletionAnalyzer
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->fileRelocationResolver = $fileRelocationResolver;
        $this->categoryNamespaceProvider = $categoryNamespaceProvider;
        $this->renamedClassesCollector = $renamedClassesCollector;
        $this->fileInfoDeletionAnalyzer = $fileInfoDeletionAnalyzer;
    }

    /**
     * @param Node[] $nodes
     */
    public function createWithDesiredGroup(
        SmartFileInfo $oldFileInfo,
        array $nodes,
        string $desiredGroupName
    ): ?AddedFileWithNodes {
        $currentNamespace = $this->betterNodeFinder->findFirstInstanceOf($nodes, Namespace_::class);

        // file without namespace → skip
        if (! $currentNamespace instanceof Namespace_) {
            return null;
        }

        if ($currentNamespace->name === null) {
            return null;
        }

        // is already in the right group
        $currentNamespaceName = $currentNamespace->name->toString();
        if (Strings::endsWith($currentNamespaceName, '\\' . $desiredGroupName)) {
            return null;
        }

        $oldClassName = $currentNamespaceName . '\\' . $this->fileInfoDeletionAnalyzer->clearNameFromTestingPrefix(
            $oldFileInfo->getBasenameWithoutSuffix()
        );

        // change namespace to new one
        $newNamespaceName = $this->createNewNamespaceName($desiredGroupName, $currentNamespace);
        $newClassName = $this->createNewClassName($oldFileInfo, $newNamespaceName);

        // classes are identical, no rename
        if ($oldClassName === $newClassName) {
            return null;
        }

        if (Strings::match($oldClassName, '#\b' . $desiredGroupName . '\b#')) {
            return null;
        }

        // 1. rename namespace
        $this->renameNamespace($nodes, $newNamespaceName);

        // 2. return changed nodes and new file destination
        $newFileDestination = $this->fileRelocationResolver->createNewFileDestination(
            $oldFileInfo,
            $desiredGroupName,
            $this->categoryNamespaceProvider->provide()
        );

        // 3. update fully qualifed name of the class like - will be used further
        $classLike = $this->betterNodeFinder->findFirstInstanceOf($nodes, ClassLike::class);
        if (! $classLike instanceof ClassLike) {
            return null;
        }

        // clone to prevent deep override
        $classLike = clone $classLike;
        $classLike->namespacedName = new FullyQualified($newClassName);

        $this->renamedClassesCollector->addClassRename($oldClassName, $newClassName);

        return new AddedFileWithNodes($newFileDestination, $nodes);
    }

    private function createNewNamespaceName(string $desiredGroupName, Namespace_ $currentNamespace): string
    {
        return $this->fileRelocationResolver->resolveNewNamespaceName(
            $currentNamespace,
            $desiredGroupName,
            $this->categoryNamespaceProvider->provide()
        );
    }

    private function createNewClassName(SmartFileInfo $smartFileInfo, string $newNamespaceName): string
    {
        $basename = $this->fileInfoDeletionAnalyzer->clearNameFromTestingPrefix(
            $smartFileInfo->getBasenameWithoutSuffix()
        );
        return $newNamespaceName . '\\' . $basename;
    }

    /**
     * @param Node[] $nodes
     */
    private function renameNamespace(array $nodes, string $newNamespaceName): void
    {
        foreach ($nodes as $node) {
            if (! $node instanceof Namespace_) {
                continue;
            }

            $node->name = new Name($newNamespaceName);
        }
    }
}
