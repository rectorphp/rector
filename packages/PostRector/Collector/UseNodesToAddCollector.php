<?php

declare (strict_types=1);
namespace Rector\PostRector\Collector;

use PhpParser\Node;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\PostRector\Contract\Collector\NodeCollectorInterface;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use RectorPrefix20220609\Symplify\SmartFileSystem\SmartFileInfo;
final class UseNodesToAddCollector implements NodeCollectorInterface
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
    public function __construct(CurrentFileProvider $currentFileProvider, UseImportsResolver $useImportsResolver)
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
    public function addFunctionUseImport(FullyQualifiedObjectType $fullyQualifiedObjectType) : void
    {
        /** @var File $file */
        $file = $this->currentFileProvider->getFile();
        $this->functionUseImportTypesInFilePath[$file->getFilePath()][] = $fullyQualifiedObjectType;
    }
    /**
     * @return AliasedObjectType[]|FullyQualifiedObjectType[]
     */
    public function getUseImportTypesByNode(File $file, Node $node) : array
    {
        $filePath = $file->getFilePath();
        $objectTypes = $this->useImportTypesInFilePath[$filePath] ?? [];
        $uses = $this->useImportsResolver->resolveForNode($node);
        foreach ($uses as $use) {
            $prefix = $this->useImportsResolver->resolvePrefix($use);
            foreach ($use->uses as $useUse) {
                if ($useUse->alias !== null) {
                    $objectTypes[] = new AliasedObjectType($useUse->alias->toString(), $prefix . $useUse->name);
                } else {
                    $objectTypes[] = new FullyQualifiedObjectType($prefix . $useUse->name);
                }
            }
        }
        return $objectTypes;
    }
    public function hasImport(File $file, Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType) : bool
    {
        $useImports = $this->getUseImportTypesByNode($file, $node);
        foreach ($useImports as $useImport) {
            if ($useImport->equals($fullyQualifiedObjectType)) {
                return \true;
            }
        }
        return \false;
    }
    public function isShortImported(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType) : bool
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
    public function isImportShortable(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType) : bool
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
    public function getObjectImportsByFileInfo(SmartFileInfo $smartFileInfo) : array
    {
        return $this->useImportTypesInFilePath[$smartFileInfo->getRealPath()] ?? [];
    }
    /**
     * @return FullyQualifiedObjectType[]
     */
    public function getFunctionImportsByFileInfo(SmartFileInfo $smartFileInfo) : array
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
