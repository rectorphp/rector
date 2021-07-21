<?php

declare (strict_types=1);
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
final class UseNodesToAddCollector implements \Rector\PostRector\Contract\Collector\NodeCollectorInterface
{
    /**
     * @var array<string, FullyQualifiedObjectType[]>
     */
    private $functionUseImportTypesInFilePath = [];
    /**
     * @var array<string, FullyQualifiedObjectType[]|AliasedObjectType[]>
     */
    private $useImportTypesInFilePath = [];
    /**
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    public function __construct(\Rector\Core\Provider\CurrentFileProvider $currentFileProvider)
    {
        $this->currentFileProvider = $currentFileProvider;
    }
    public function isActive() : bool
    {
        return $this->useImportTypesInFilePath !== [] || $this->functionUseImportTypesInFilePath !== [];
    }
    /**
     * @param \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType|\Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType $objectType
     */
    public function addUseImport($objectType) : void
    {
        $file = $this->currentFileProvider->getFile();
        $smartFileInfo = $file->getSmartFileInfo();
        $this->useImportTypesInFilePath[$smartFileInfo->getRealPath()][] = $objectType;
    }
    /**
     * @param \PhpParser\Node $node
     * @param \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType
     */
    public function addFunctionUseImport($node, $fullyQualifiedObjectType) : void
    {
        $file = $this->currentFileProvider->getFile();
        $smartFileInfo = $file->getSmartFileInfo();
        $this->functionUseImportTypesInFilePath[$smartFileInfo->getRealPath()][] = $fullyQualifiedObjectType;
    }
    /**
     * @return AliasedObjectType[]|FullyQualifiedObjectType[]
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \PhpParser\Node $node
     */
    public function getUseImportTypesByNode($file, $node) : array
    {
        $fileInfo = $file->getSmartFileInfo();
        $filePath = $fileInfo->getRealPath();
        $objectTypes = $this->useImportTypesInFilePath[$filePath] ?? [];
        /** @var Use_[] $useNodes */
        $useNodes = (array) $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::USE_NODES);
        foreach ($useNodes as $useNode) {
            foreach ($useNode->uses as $useUse) {
                if ($useUse->alias !== null) {
                    $objectTypes[] = new \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType($useUse->alias->toString(), (string) $useUse->name);
                } else {
                    $objectTypes[] = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType((string) $useUse->name);
                }
            }
        }
        return $objectTypes;
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \PhpParser\Node $node
     * @param \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType
     */
    public function hasImport($file, $node, $fullyQualifiedObjectType) : bool
    {
        $useImports = $this->getUseImportTypesByNode($file, $node);
        foreach ($useImports as $useImport) {
            if ($useImport->equals($fullyQualifiedObjectType)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType
     */
    public function isShortImported($file, $fullyQualifiedObjectType) : bool
    {
        $fileInfo = $file->getSmartFileInfo();
        $filePath = $fileInfo->getRealPath();
        $shortName = $fullyQualifiedObjectType->getShortName();
        if ($this->isShortClassImported($file, $shortName)) {
            return \true;
        }
        $fileFunctionUseImportTypes = $this->functionUseImportTypesInFilePath[$filePath] ?? [];
        foreach ($fileFunctionUseImportTypes as $fileFunctionUseImportType) {
            if ($fileFunctionUseImportType->getShortName() === $fullyQualifiedObjectType->getShortName()) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType
     */
    public function isImportShortable($file, $fullyQualifiedObjectType) : bool
    {
        $fileInfo = $file->getSmartFileInfo();
        $filePath = $fileInfo->getRealPath();
        $fileUseImportTypes = $this->useImportTypesInFilePath[$filePath] ?? [];
        foreach ($fileUseImportTypes as $fileUseImportType) {
            if ($fullyQualifiedObjectType->equals($fileUseImportType)) {
                return \true;
            }
        }
        $functionImports = $this->functionUseImportTypesInFilePath[$filePath] ?? [];
        foreach ($functionImports as $functionImport) {
            if ($fullyQualifiedObjectType->equals($functionImport)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @return AliasedObjectType[]|FullyQualifiedObjectType[]
     * @param \Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo
     */
    public function getObjectImportsByFileInfo($smartFileInfo) : array
    {
        return $this->useImportTypesInFilePath[$smartFileInfo->getRealPath()] ?? [];
    }
    /**
     * @return FullyQualifiedObjectType[]
     * @param \Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo
     */
    public function getFunctionImportsByFileInfo($smartFileInfo) : array
    {
        return $this->functionUseImportTypesInFilePath[$smartFileInfo->getRealPath()] ?? [];
    }
    private function isShortClassImported(\Rector\Core\ValueObject\Application\File $file, string $shortName) : bool
    {
        $fileInfo = $file->getSmartFileInfo();
        $realPath = $fileInfo->getRealPath();
        $fileUseImports = $this->useImportTypesInFilePath[$realPath] ?? [];
        foreach ($fileUseImports as $fileUseImport) {
            if ($fileUseImport->getShortName() === $shortName) {
                return \true;
            }
        }
        return \false;
    }
}
