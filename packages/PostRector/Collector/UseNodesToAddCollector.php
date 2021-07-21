<?php

declare(strict_types=1);

namespace Rector\PostRector\Collector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Contract\Collector\NodeCollectorInterface;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UseNodesToAddCollector implements NodeCollectorInterface
{
    /**
     * @var array<string, FullyQualifiedObjectType[]>
     */
    private array $functionUseImportTypesInFilePath = [];

    /**
     * @var array<string, FullyQualifiedObjectType[]|AliasedObjectType[]>
     */
    private array $useImportTypesInFilePath = [];

    public function __construct(
        private CurrentFileProvider $currentFileProvider
    ) {
    }

    public function isActive(): bool
    {
        return $this->useImportTypesInFilePath !== [] || $this->functionUseImportTypesInFilePath !== [];
    }

    public function addUseImport(FullyQualifiedObjectType | AliasedObjectType $objectType): void
    {
        $file = $this->currentFileProvider->getFile();
        $smartFileInfo = $file->getSmartFileInfo();

        $this->useImportTypesInFilePath[$smartFileInfo->getRealPath()][] = $objectType;
    }

    public function addFunctionUseImport(Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): void
    {
        $file = $this->currentFileProvider->getFile();
        $smartFileInfo = $file->getSmartFileInfo();

        $this->functionUseImportTypesInFilePath[$smartFileInfo->getRealPath()][] = $fullyQualifiedObjectType;
    }

    /**
     * @return AliasedObjectType[]|FullyQualifiedObjectType[]
     */
    public function getUseImportTypesByNode(File $file, Node $node): array
    {
        $fileInfo = $file->getSmartFileInfo();
        $filePath = $fileInfo->getRealPath();

        $objectTypes = $this->useImportTypesInFilePath[$filePath] ?? [];

        /** @var Use_[] $useNodes */
        $useNodes = (array) $node->getAttribute(AttributeKey::USE_NODES);
        foreach ($useNodes as $useNode) {
            foreach ($useNode->uses as $useUse) {
                if ($useUse->alias === null) {
                    $objectTypes[] = new FullyQualifiedObjectType((string) $useUse->name);
                } else {
                    $objectTypes[] = new AliasedObjectType($useUse->alias->toString(), (string) $useUse->name);
                }
            }
        }

        return $objectTypes;
    }

    public function hasImport(File $file, Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $useImports = $this->getUseImportTypesByNode($file, $node);

        foreach ($useImports as $useImport) {
            if ($useImport->equals($fullyQualifiedObjectType)) {
                return true;
            }
        }

        return false;
    }

    public function isShortImported(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $fileInfo = $file->getSmartFileInfo();
        $filePath = $fileInfo->getRealPath();

        $shortName = $fullyQualifiedObjectType->getShortName();

        if ($this->isShortClassImported($file, $shortName)) {
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

    public function isImportShortable(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $fileInfo = $file->getSmartFileInfo();
        $filePath = $fileInfo->getRealPath();

        $fileUseImportTypes = $this->useImportTypesInFilePath[$filePath] ?? [];

        foreach ($fileUseImportTypes as $fileUseImportType) {
            if ($fullyQualifiedObjectType->equals($fileUseImportType)) {
                return true;
            }
        }

        $functionImports = $this->functionUseImportTypesInFilePath[$filePath] ?? [];
        foreach ($functionImports as $functionImport) {
            if ($fullyQualifiedObjectType->equals($functionImport)) {
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

    private function isShortClassImported(File $file, string $shortName): bool
    {
        $fileInfo = $file->getSmartFileInfo();
        $realPath = $fileInfo->getRealPath();

        $fileUseImports = $this->useImportTypesInFilePath[$realPath] ?? [];

        foreach ($fileUseImports as $fileUseImport) {
            if ($fileUseImport->getShortName() === $shortName) {
                return true;
            }
        }

        return false;
    }
}
