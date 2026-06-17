<?php

declare (strict_types=1);
namespace Rector\PostRector\Collector;

use Rector\Application\Provider\CurrentFileProvider;
use Rector\PhpParser\Node\FileNode;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\ValueObject\Application\File;
/**
 * @api Backward-compatibility layer only.
 *
 * @deprecated since Rector 2.4.7, use the FileNode directly as the single source of truth for imports.
 *             The standalone collector service is gone; import state now lives on the FileNode of each File.
 *
 * Upgrade example:
 *
 * Before:
 *
 *     public function __construct(
 *         private UseNodesToAddCollector $useNodesToAddCollector,
 *     ) {
 *     }
 *
 *     public function refactor(File $file, FullyQualifiedObjectType $objectType): void
 *     {
 *         if ($this->useNodesToAddCollector->hasImport($file, $objectType)) {
 *             return;
 *         }
 *
 *         $this->useNodesToAddCollector->addUseImport($objectType);
 *     }
 *
 * After:
 *
 *     public function refactor(File $file, FullyQualifiedObjectType $objectType): void
 *     {
 *         $fileNode = $file->getFileNode();
 *         if (! $fileNode instanceof FileNode) {
 *             return;
 *         }
 *
 *         if ($fileNode->hasImport($objectType)) {
 *             return;
 *         }
 *
 *         $fileNode->getPendingImports()
 *             ->addUseImport($objectType);
 *     }
 *
 * Method mapping:
 *   addUseImport($t)                 -> $file->getFileNode()->getPendingImports()->addUseImport($t)
 *   addConstantUseImport($t)         -> $file->getFileNode()->getPendingImports()->addConstantUseImport($t)
 *   addFunctionUseImport($t)         -> $file->getFileNode()->getPendingImports()->addFunctionUseImport($t)
 *   getUseImportTypesByNode($file)   -> $file->getFileNode()->resolveUsedImportTypes()
 *   hasImport($file, $t)             -> $file->getFileNode()->hasImport($t)
 *   isShortImported($file, $t)       -> $file->getFileNode()->getPendingImports()->isShortImported($t)
 *   isImportShortable($file, $t)     -> $file->getFileNode()->getPendingImports()->isImportShortable($t)
 *   getObjectImportsByFilePath()     -> $file->getFileNode()->getPendingImports()->getUseImports()
 *   getConstantImportsByFilePath()   -> $file->getFileNode()->getPendingImports()->getConstantImports()
 *   getFunctionImportsByFilePath()   -> $file->getFileNode()->getPendingImports()->getFunctionImports()
 */
final class UseNodesToAddCollector
{
    /**
     * @readonly
     */
    private CurrentFileProvider $currentFileProvider;
    public function __construct(CurrentFileProvider $currentFileProvider)
    {
        $this->currentFileProvider = $currentFileProvider;
    }
    /**
     * @api
     *
     * @deprecated Use $file->getFileNode()->getPendingImports()->addUseImport($type) instead.
     */
    public function addUseImport(FullyQualifiedObjectType $fullyQualifiedObjectType): void
    {
        $this->warn('addUseImport()', '$file->getFileNode()->getPendingImports()->addUseImport($type)');
        $fileNode = $this->resolveCurrentFileNode();
        if (!$fileNode instanceof FileNode) {
            return;
        }
        $fileNode->getPendingImports()->addUseImport($fullyQualifiedObjectType);
    }
    /**
     * @api
     *
     * @deprecated Use $file->getFileNode()->getPendingImports()->addConstantUseImport($type) instead.
     */
    public function addConstantUseImport(FullyQualifiedObjectType $fullyQualifiedObjectType): void
    {
        $this->warn('addConstantUseImport()', '$file->getFileNode()->getPendingImports()->addConstantUseImport($type)');
        $fileNode = $this->resolveCurrentFileNode();
        if (!$fileNode instanceof FileNode) {
            return;
        }
        $fileNode->getPendingImports()->addConstantUseImport($fullyQualifiedObjectType);
    }
    /**
     * @api
     *
     * @deprecated Use $file->getFileNode()->getPendingImports()->addFunctionUseImport($type) instead.
     */
    public function addFunctionUseImport(FullyQualifiedObjectType $fullyQualifiedObjectType): void
    {
        $this->warn('addFunctionUseImport()', '$file->getFileNode()->getPendingImports()->addFunctionUseImport($type)');
        $fileNode = $this->resolveCurrentFileNode();
        if (!$fileNode instanceof FileNode) {
            return;
        }
        $fileNode->getPendingImports()->addFunctionUseImport($fullyQualifiedObjectType);
    }
    /**
     * @api
     *
     * @deprecated Use $file->getFileNode()->resolveUsedImportTypes() instead.
     *
     * @return array<AliasedObjectType|FullyQualifiedObjectType>
     */
    public function getUseImportTypesByNode(File $file): array
    {
        $this->warn('getUseImportTypesByNode()', '$file->getFileNode()->resolveUsedImportTypes()');
        $fileNode = $file->getFileNode();
        if (!$fileNode instanceof FileNode) {
            return [];
        }
        return $fileNode->resolveUsedImportTypes();
    }
    /**
     * @api
     *
     * @deprecated Use $file->getFileNode()->hasImport($type) instead.
     */
    public function hasImport(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $this->warn('hasImport()', '$file->getFileNode()->hasImport($type)');
        $fileNode = $file->getFileNode();
        if (!$fileNode instanceof FileNode) {
            return \false;
        }
        return $fileNode->hasImport($fullyQualifiedObjectType);
    }
    /**
     * @api
     *
     * @deprecated Use $file->getFileNode()->getPendingImports()->isShortImported($type) instead.
     */
    public function isShortImported(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $this->warn('isShortImported()', '$file->getFileNode()->getPendingImports()->isShortImported($type)');
        $fileNode = $file->getFileNode();
        if (!$fileNode instanceof FileNode) {
            return \false;
        }
        return $fileNode->getPendingImports()->isShortImported($fullyQualifiedObjectType);
    }
    /**
     * @api
     *
     * @deprecated Use $file->getFileNode()->getPendingImports()->isImportShortable($type) instead.
     */
    public function isImportShortable(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $this->warn('isImportShortable()', '$file->getFileNode()->getPendingImports()->isImportShortable($type)');
        $fileNode = $file->getFileNode();
        if (!$fileNode instanceof FileNode) {
            return \false;
        }
        return $fileNode->getPendingImports()->isImportShortable($fullyQualifiedObjectType);
    }
    /**
     * @api
     *
     * @deprecated Use $file->getFileNode()->getPendingImports()->getUseImports() instead.
     *
     * @return FullyQualifiedObjectType[]
     */
    public function getObjectImportsByFilePath(string $filePath): array
    {
        $this->warn('getObjectImportsByFilePath()', '$file->getFileNode()->getPendingImports()->getUseImports()');
        $fileNode = $this->resolveCurrentFileNode();
        if (!$fileNode instanceof FileNode) {
            return [];
        }
        return $fileNode->getPendingImports()->getUseImports();
    }
    /**
     * @api
     *
     * @deprecated Use $file->getFileNode()->getPendingImports()->getConstantImports() instead.
     *
     * @return FullyQualifiedObjectType[]
     */
    public function getConstantImportsByFilePath(string $filePath): array
    {
        $this->warn('getConstantImportsByFilePath()', '$file->getFileNode()->getPendingImports()->getConstantImports()');
        $fileNode = $this->resolveCurrentFileNode();
        if (!$fileNode instanceof FileNode) {
            return [];
        }
        return $fileNode->getPendingImports()->getConstantImports();
    }
    /**
     * @api
     *
     * @deprecated Use $file->getFileNode()->getPendingImports()->getFunctionImports() instead.
     *
     * @return FullyQualifiedObjectType[]
     */
    public function getFunctionImportsByFilePath(string $filePath): array
    {
        $this->warn('getFunctionImportsByFilePath()', '$file->getFileNode()->getPendingImports()->getFunctionImports()');
        $fileNode = $this->resolveCurrentFileNode();
        if (!$fileNode instanceof FileNode) {
            return [];
        }
        return $fileNode->getPendingImports()->getFunctionImports();
    }
    private function resolveCurrentFileNode(): ?FileNode
    {
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            return null;
        }
        return $file->getFileNode();
    }
    private function warn(string $method, string $replacement): void
    {
        trigger_error(sprintf('UseNodesToAddCollector::%s is deprecated and will be removed. Use "%s" instead, via $file->getFileNode().', $method, $replacement), \E_USER_DEPRECATED);
    }
}
