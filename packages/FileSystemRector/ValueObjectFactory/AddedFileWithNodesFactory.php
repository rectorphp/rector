<?php

declare (strict_types=1);
namespace Rector\FileSystemRector\ValueObjectFactory;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Autodiscovery\Configuration\CategoryNamespaceProvider;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Util\StringUtils;
use Rector\Core\ValueObject\Application\File;
use Rector\FileSystemRector\ValueObject\AddedFileWithNodes;
use Rector\PSR4\FileInfoAnalyzer\FileInfoDeletionAnalyzer;
use Rector\PSR4\FileRelocationResolver;
use Symplify\SmartFileSystem\SmartFileInfo;
final class AddedFileWithNodesFactory
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Autodiscovery\Configuration\CategoryNamespaceProvider
     */
    private $categoryNamespaceProvider;
    /**
     * @readonly
     * @var \Rector\PSR4\FileRelocationResolver
     */
    private $fileRelocationResolver;
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    /**
     * @readonly
     * @var \Rector\PSR4\FileInfoAnalyzer\FileInfoDeletionAnalyzer
     */
    private $fileInfoDeletionAnalyzer;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Autodiscovery\Configuration\CategoryNamespaceProvider $categoryNamespaceProvider, \Rector\PSR4\FileRelocationResolver $fileRelocationResolver, \Rector\Core\Configuration\RenamedClassesDataCollector $renamedClassesDataCollector, \Rector\PSR4\FileInfoAnalyzer\FileInfoDeletionAnalyzer $fileInfoDeletionAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->categoryNamespaceProvider = $categoryNamespaceProvider;
        $this->fileRelocationResolver = $fileRelocationResolver;
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->fileInfoDeletionAnalyzer = $fileInfoDeletionAnalyzer;
    }
    public function createWithDesiredGroup(\Symplify\SmartFileSystem\SmartFileInfo $oldFileInfo, \Rector\Core\ValueObject\Application\File $file, string $desiredGroupName) : ?\Rector\FileSystemRector\ValueObject\AddedFileWithNodes
    {
        $fileNodes = $file->getNewStmts();
        $currentNamespace = $this->betterNodeFinder->findFirstInstanceOf($fileNodes, \PhpParser\Node\Stmt\Namespace_::class);
        // file without namespace → skip
        if (!$currentNamespace instanceof \PhpParser\Node\Stmt\Namespace_) {
            return null;
        }
        if ($currentNamespace->name === null) {
            return null;
        }
        // is already in the right group
        $currentNamespaceName = $currentNamespace->name->toString();
        if (\substr_compare($currentNamespaceName, '\\' . $desiredGroupName, -\strlen('\\' . $desiredGroupName)) === 0) {
            return null;
        }
        $oldClassName = $currentNamespaceName . '\\' . $this->fileInfoDeletionAnalyzer->clearNameFromTestingPrefix($oldFileInfo->getBasenameWithoutSuffix());
        // change namespace to new one
        $newNamespaceName = $this->createNewNamespaceName($desiredGroupName, $currentNamespace);
        $newClassName = $this->createNewClassName($oldFileInfo, $newNamespaceName);
        // classes are identical, no rename
        if ($oldClassName === $newClassName) {
            return null;
        }
        if (\Rector\Core\Util\StringUtils::isMatch($oldClassName, '#\\b' . $desiredGroupName . '\\b#')) {
            return null;
        }
        // 1. rename namespace
        $this->renameNamespace($file->getNewStmts(), $newNamespaceName);
        // 2. return changed nodes and new file destination
        $newFileDestination = $this->fileRelocationResolver->createNewFileDestination($oldFileInfo, $desiredGroupName, $this->categoryNamespaceProvider->provide());
        // 3. update fully qualifed name of the class like - will be used further
        $classLike = $this->betterNodeFinder->findFirstInstanceOf($fileNodes, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        // clone to prevent deep override
        $classLike = clone $classLike;
        $classLike->namespacedName = new \PhpParser\Node\Name\FullyQualified($newClassName);
        $this->renamedClassesDataCollector->addOldToNewClass($oldClassName, $newClassName);
        return new \Rector\FileSystemRector\ValueObject\AddedFileWithNodes($newFileDestination, $fileNodes);
    }
    private function createNewNamespaceName(string $desiredGroupName, \PhpParser\Node\Stmt\Namespace_ $currentNamespace) : string
    {
        return $this->fileRelocationResolver->resolveNewNamespaceName($currentNamespace, $desiredGroupName, $this->categoryNamespaceProvider->provide());
    }
    private function createNewClassName(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo, string $newNamespaceName) : string
    {
        $basename = $this->fileInfoDeletionAnalyzer->clearNameFromTestingPrefix($smartFileInfo->getBasenameWithoutSuffix());
        return $newNamespaceName . '\\' . $basename;
    }
    /**
     * @param Node[] $nodes
     */
    private function renameNamespace(array $nodes, string $newNamespaceName) : void
    {
        foreach ($nodes as $node) {
            if (!$node instanceof \PhpParser\Node\Stmt\Namespace_) {
                continue;
            }
            $node->name = new \PhpParser\Node\Name($newNamespaceName);
        }
    }
}
