<?php

declare (strict_types=1);
namespace Rector\PostRector\Collector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Type\ObjectType;
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
     * @var string[][]
     */
    private $removedShortUsesInFilePath = [];
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
     * @param FullyQualifiedObjectType|AliasedObjectType $objectType
     */
    public function addUseImport(\PhpParser\Node $positionNode, \PHPStan\Type\ObjectType $objectType) : void
    {
        $file = $this->currentFileProvider->getFile();
        $smartFileInfo = $file->getSmartFileInfo();
        $this->useImportTypesInFilePath[$smartFileInfo->getRealPath()][] = $objectType;
    }
    public function addFunctionUseImport(\PhpParser\Node $node, \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType) : void
    {
        $file = $this->currentFileProvider->getFile();
        $smartFileInfo = $file->getSmartFileInfo();
        $this->functionUseImportTypesInFilePath[$smartFileInfo->getRealPath()][] = $fullyQualifiedObjectType;
    }
    public function removeShortUse(\PhpParser\Node $node, string $shortUse) : void
    {
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof \Rector\Core\ValueObject\Application\File) {
            return;
        }
        $smartFileInfo = $file->getSmartFileInfo();
        $this->removedShortUsesInFilePath[$smartFileInfo->getRealPath()][] = $shortUse;
    }
    public function clear(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : void
    {
        // clear applied imports, so isActive() doesn't return any false positives
        unset($this->useImportTypesInFilePath[$smartFileInfo->getRealPath()], $this->functionUseImportTypesInFilePath[$smartFileInfo->getRealPath()]);
    }
    /**
     * @return AliasedObjectType[]|FullyQualifiedObjectType[]
     */
    public function getUseImportTypesByNode(\PhpParser\Node $node) : array
    {
        $filePath = $this->getRealPathFromNode();
        $objectTypes = $this->useImportTypesInFilePath[$filePath] ?? [];
        /** @var Use_[] $useNodes */
        $useNodes = (array) $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::USE_NODES);
        foreach ($useNodes as $useNode) {
            foreach ($useNode->uses as $useUse) {
                if ($useUse->alias === null) {
                    $objectTypes[] = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType((string) $useUse->name);
                } else {
                    $objectTypes[] = new \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType($useUse->alias->toString(), (string) $useUse->name);
                }
            }
        }
        return $objectTypes;
    }
    public function hasImport(\PhpParser\Node $node, \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType) : bool
    {
        $useImports = $this->getUseImportTypesByNode($node);
        foreach ($useImports as $useImport) {
            if ($useImport->equals($fullyQualifiedObjectType)) {
                return \true;
            }
        }
        return \false;
    }
    public function isShortImported(\PhpParser\Node $node, \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType) : bool
    {
        $filePath = $this->getRealPathFromNode();
        if ($filePath === null) {
            return \false;
        }
        $shortName = $fullyQualifiedObjectType->getShortName();
        if ($this->isShortClassImported($filePath, $shortName)) {
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
    public function isImportShortable(\PhpParser\Node $node, \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType) : bool
    {
        $filePath = $this->getRealPathFromNode();
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
     */
    public function getObjectImportsByFileInfo(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : array
    {
        return $this->useImportTypesInFilePath[$smartFileInfo->getRealPath()] ?? [];
    }
    /**
     * @return FullyQualifiedObjectType[]
     */
    public function getFunctionImportsByFileInfo(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : array
    {
        return $this->functionUseImportTypesInFilePath[$smartFileInfo->getRealPath()] ?? [];
    }
    /**
     * @return string[]
     */
    public function getShortUsesByFileInfo(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : array
    {
        return $this->removedShortUsesInFilePath[$smartFileInfo->getRealPath()] ?? [];
    }
    private function getRealPathFromNode() : ?string
    {
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof \Rector\Core\ValueObject\Application\File) {
            return null;
        }
        $smartFileInfo = $file->getSmartFileInfo();
        return $smartFileInfo->getRealPath();
    }
    private function isShortClassImported(string $filePath, string $shortName) : bool
    {
        $fileUseImports = $this->useImportTypesInFilePath[$filePath] ?? [];
        foreach ($fileUseImports as $fileUseImport) {
            if ($fileUseImport->getShortName() === $shortName) {
                return \true;
            }
        }
        return \false;
    }
}
