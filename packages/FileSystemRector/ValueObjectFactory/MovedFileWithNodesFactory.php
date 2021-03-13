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
use Rector\FileSystemRector\ValueObject\MovedFileWithNodes;
use Rector\PSR4\FileRelocationResolver;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MovedFileWithNodesFactory
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

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        CategoryNamespaceProvider $categoryNamespaceProvider,
        FileRelocationResolver $fileRelocationResolver
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->fileRelocationResolver = $fileRelocationResolver;
        $this->categoryNamespaceProvider = $categoryNamespaceProvider;
    }

    /**
     * @param Node[] $nodes
     */
    public function createWithDesiredGroup(
        SmartFileInfo $oldFileInfo,
        array $nodes,
        string $desiredGroupName
    ): ?MovedFileWithNodes {
        $currentNamespace = $this->betterNodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
        // file without namespace → skip
        if (! $currentNamespace instanceof Namespace_) {
            return null;
        }
        if ($currentNamespace->name === null) {
            return null;
        }

        // is already in the right group
        $currentNamespaceName = (string) $currentNamespace->name;
        if (Strings::endsWith($currentNamespaceName, '\\' . $desiredGroupName)) {
            return null;
        }

        $oldClassName = $currentNamespaceName . '\\' . $oldFileInfo->getBasenameWithoutSuffix();

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

        return new MovedFileWithNodes($nodes, $newFileDestination, $oldFileInfo, $oldClassName, $newClassName);
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
        return $newNamespaceName . '\\' . $smartFileInfo->getBasenameWithoutSuffix();
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

            // prevent namespace override
            $node = clone $node;

            $node->name = new Name($newNamespaceName);
            break;
        }
    }
}
