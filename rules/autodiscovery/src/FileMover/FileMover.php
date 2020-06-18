<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\FileMover;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Autodiscovery\Configuration\CategoryNamespaceProvider;
use Rector\Autodiscovery\ValueObject\NodesWithFileDestinationValueObject;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\PSR4\FileRelocationResolver;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FileMover
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
        FileRelocationResolver $fileRelocationResolver,
        CategoryNamespaceProvider $categoryNamespaceProvider
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->fileRelocationResolver = $fileRelocationResolver;
        $this->categoryNamespaceProvider = $categoryNamespaceProvider;
    }

    /**
     * @param Node[] $nodes
     */
    public function createMovedNodesAndFilePath(
        SmartFileInfo $smartFileInfo,
        array $nodes,
        string $desiredGroupName
    ): ?NodesWithFileDestinationValueObject {
        /** @var Namespace_|null $currentNamespace */
        $currentNamespace = $this->betterNodeFinder->findFirstInstanceOf($nodes, Namespace_::class);

        // file without namespace → skip
        if ($currentNamespace === null || $currentNamespace->name === null) {
            return null;
        }

        $currentClassName = $currentNamespace->name->toString() . '\\' . $smartFileInfo->getBasenameWithoutSuffix();

        // is already in the right group
        if (Strings::endsWith((string) $currentNamespace->name, '\\' . $desiredGroupName)) {
            return null;
        }

        // change namespace to new one
        $newNamespaceName = $this->createNewNamespaceName($desiredGroupName, $currentNamespace);
        $newClassName = $this->createNewClassName($smartFileInfo, $newNamespaceName);

        // classes are identical, no rename
        if ($currentClassName === $newClassName) {
            return null;
        }

        // 1. rename namespace
        foreach ($nodes as $node) {
            if ($node instanceof Namespace_) {
                $node->name = new Name($newNamespaceName);
            }
        }

        // 2. return changed nodes and new file destination
        $newFileDestination = $this->fileRelocationResolver->createNewFileDestination(
            $smartFileInfo,
            $desiredGroupName,
            $this->categoryNamespaceProvider->provide()
        );

        // 3. update fully qualifed name of the class like - will be used further
        /** @var ClassLike $classLike */
        $classLike = $this->betterNodeFinder->findFirstInstanceOf($nodes, ClassLike::class);
        $classLike->namespacedName = new FullyQualified($newClassName);

        return new NodesWithFileDestinationValueObject($nodes, $newFileDestination, $currentClassName, $newClassName);
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
}
