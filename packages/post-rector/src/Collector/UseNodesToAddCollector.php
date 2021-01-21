<?php

declare(strict_types=1);

namespace Rector\PostRector\Collector;

use PhpParser\Node;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Contract\Collector\NodeCollectorInterface;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UseNodesToAddCollector implements NodeCollectorInterface
{
    /**
     * @var string[][]
     */
    private $removedShortUsesInFilePath = [];

    /**
     * @var FullyQualifiedObjectType[][]
     */
    private $functionUseImportTypesInFilePath = [];

    /**
     * @var CurrentFileInfoProvider
     */
    private $currentFileInfoProvider;

    /**
     * @var FullyQualifiedObjectType[][]|AliasedObjectType[][]
     */
    private $useImportTypesInFilePath = [];

    public function __construct(CurrentFileInfoProvider $currentFileInfoProvider)
    {
        $this->currentFileInfoProvider = $currentFileInfoProvider;
    }

    public function isActive(): bool
    {
        return $this->useImportTypesInFilePath !== [] || $this->functionUseImportTypesInFilePath !== [];
    }

    /**
     * @param FullyQualifiedObjectType|AliasedObjectType $objectType
     */
    public function addUseImport(Node $positionNode, ObjectType $objectType): void
    {
        /** @var SmartFileInfo|null $fileInfo */
        $fileInfo = $positionNode->getAttribute(AttributeKey::FILE_INFO);
        if (! $fileInfo instanceof SmartFileInfo) {
            // fallback for freshly created Name nodes
            $fileInfo = $this->currentFileInfoProvider->getSmartFileInfo();
            if (! $fileInfo instanceof SmartFileInfo) {
                return;
            }
        }

        $this->useImportTypesInFilePath[$fileInfo->getRealPath()][] = $objectType;
    }

    public function addFunctionUseImport(Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): void
    {
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if (! $fileInfo instanceof SmartFileInfo) {
            return;
        }

        $this->functionUseImportTypesInFilePath[$fileInfo->getRealPath()][] = $fullyQualifiedObjectType;
    }

    public function removeShortUse(Node $node, string $shortUse): void
    {
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if (! $fileInfo instanceof SmartFileInfo) {
            return;
        }

        $this->removedShortUsesInFilePath[$fileInfo->getRealPath()][] = $shortUse;
    }

    public function clear(SmartFileInfo $smartFileInfo): void
    {
        // clear applied imports, so isActive() doesn't return any false positives
        unset($this->useImportTypesInFilePath[$smartFileInfo->getRealPath()], $this->functionUseImportTypesInFilePath[$smartFileInfo->getRealPath()]);
    }

    /**
     * @return AliasedObjectType[]|FullyQualifiedObjectType[]
     */
    public function getUseImportTypesByNode(Node $node): array
    {
        $filePath = $this->getRealPathFromNode($node);

        return $this->useImportTypesInFilePath[$filePath] ?? [];
    }

    public function hasImport(Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $useImports = $this->getUseImportTypesByNode($node);

        foreach ($useImports as $useImport) {
            if ($useImport->equals($fullyQualifiedObjectType)) {
                return true;
            }
        }

        return false;
    }

    public function isShortImported(Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $filePath = $this->getRealPathFromNode($node);
        if ($filePath === null) {
            return false;
        }

        $shortName = $fullyQualifiedObjectType->getShortName();

        if ($this->isShortClassImported($filePath, $shortName)) {
            return true;
        }

        $fileFunctionUseImportTypes = $this->functionUseImportTypesInFilePath[$filePath] ?? [];
        foreach ($fileFunctionUseImportTypes as $fileFunctionUseImportType) {
            if ($fileFunctionUseImportType->getShortName() === $fullyQualifiedObjectType->getShortName()) {
                return true;
            }
        }

        return false;
    }

    public function isImportShortable(Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $filePath = $this->getRealPathFromNode($node);

        $fileUseImportTypes = $this->useImportTypesInFilePath[$filePath] ?? [];

        foreach ($fileUseImportTypes as $useImportType) {
            if ($fullyQualifiedObjectType->equals($useImportType)) {
                return true;
            }
        }

        $functionImports = $this->functionUseImportTypesInFilePath[$filePath] ?? [];
        foreach ($functionImports as $useImportType) {
            if ($fullyQualifiedObjectType->equals($useImportType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return AliasedObjectType[]|FullyQualifiedObjectType[]
     */
    public function getObjectImportsByFileInfo(SmartFileInfo $smartFileInfo): array
    {
        return $this->useImportTypesInFilePath[$smartFileInfo->getRealPath()] ?? [];
    }

    /**
     * @return FullyQualifiedObjectType[]
     */
    public function getFunctionImportsByFileInfo(SmartFileInfo $smartFileInfo): array
    {
        return $this->functionUseImportTypesInFilePath[$smartFileInfo->getRealPath()] ?? [];
    }

    /**
     * @return string[]
     */
    public function getShortUsesByFileInfo(SmartFileInfo $smartFileInfo): array
    {
        return $this->removedShortUsesInFilePath[$smartFileInfo->getRealPath()] ?? [];
    }

    private function getRealPathFromNode(Node $node): ?string
    {
        /** @var SmartFileInfo|null $fileInfo */
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if ($fileInfo !== null) {
            return $fileInfo->getRealPath();
        }

        $smartFileInfo = $this->currentFileInfoProvider->getSmartFileInfo();
        if (! $smartFileInfo instanceof SmartFileInfo) {
            return null;
        }

        return $smartFileInfo->getRealPath();
    }

    private function isShortClassImported(string $filePath, string $shortName): bool
    {
        $fileUseImports = $this->useImportTypesInFilePath[$filePath] ?? [];

        foreach ($fileUseImports as $fileUseImport) {
            if ($fileUseImport->getShortName() === $shortName) {
                return true;
            }
        }

        return false;
    }
}
