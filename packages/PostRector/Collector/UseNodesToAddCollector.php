<?php

declare (strict_types=1);
namespace Rector\PostRector\Collector;

use PhpParser\Node;
use PhpParser\Node\Stmt\GroupUse;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\Naming\Naming\UseImportsResolver;
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
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    public function __construct(\Rector\Core\Provider\CurrentFileProvider $currentFileProvider, \Rector\Naming\Naming\UseImportsResolver $useImportsResolver)
    {
        $this->currentFileProvider = $currentFileProvider;
        $this->useImportsResolver = $useImportsResolver;
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
        /** @var File $file */
        $file = $this->currentFileProvider->getFile();
        $this->useImportTypesInFilePath[$file->getFilePath()][] = $objectType;
    }
    public function addFunctionUseImport(\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType) : void
    {
        /** @var File $file */
        $file = $this->currentFileProvider->getFile();
        $this->functionUseImportTypesInFilePath[$file->getFilePath()][] = $fullyQualifiedObjectType;
    }
    /**
     * @return AliasedObjectType[]|FullyQualifiedObjectType[]
     */
    public function getUseImportTypesByNode(\Rector\Core\ValueObject\Application\File $file, \PhpParser\Node $node) : array
    {
        $filePath = $file->getFilePath();
        $objectTypes = $this->useImportTypesInFilePath[$filePath] ?? [];
        $uses = $this->useImportsResolver->resolveForNode($node);
        foreach ($uses as $use) {
            $prefix = $use instanceof \PhpParser\Node\Stmt\GroupUse ? $use->prefix . '\\' : '';
            foreach ($use->uses as $useUse) {
                if ($useUse->alias !== null) {
                    $objectTypes[] = new \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType($useUse->alias->toString(), $prefix . $useUse->name);
                } else {
                    $objectTypes[] = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($prefix . $useUse->name);
                }
            }
        }
        return $objectTypes;
    }
    public function hasImport(\Rector\Core\ValueObject\Application\File $file, \PhpParser\Node $node, \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType) : bool
    {
        $useImports = $this->getUseImportTypesByNode($file, $node);
        foreach ($useImports as $useImport) {
            if ($useImport->equals($fullyQualifiedObjectType)) {
                return \true;
            }
        }
        return \false;
    }
    public function isShortImported(\Rector\Core\ValueObject\Application\File $file, \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType) : bool
    {
        $shortName = $fullyQualifiedObjectType->getShortName();
        $filePath = $file->getFilePath();
        if ($this->isShortClassImported($filePath, $shortName)) {
            return \true;
        }
        $fileFunctionUseImportTypes = $this->functionUseImportTypesInFilePath[$filePath] ?? [];
        foreach ($fileFunctionUseImportTypes as $fileFunctionUseImportType) {
            if ($fileFunctionUseImportType->getShortName() === $shortName) {
                return \true;
            }
        }
        return \false;
    }
    public function isImportShortable(\Rector\Core\ValueObject\Application\File $file, \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType) : bool
    {
        $filePath = $file->getFilePath();
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
