<?php

declare (strict_types=1);
namespace Rector\PostRector\Collector;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class UseNodesToAddCollector
{
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
    /**
     * @var array<string, FullyQualifiedObjectType[]>
     */
    private $constantUseImportTypesInFilePath = [];
    /**
     * @var array<string, FullyQualifiedObjectType[]>
     */
    private $functionUseImportTypesInFilePath = [];
    /**
     * @var array<string, FullyQualifiedObjectType[]>
     */
    private $useImportTypesInFilePath = [];
    public function __construct(CurrentFileProvider $currentFileProvider, UseImportsResolver $useImportsResolver)
    {
        $this->currentFileProvider = $currentFileProvider;
        $this->useImportsResolver = $useImportsResolver;
    }
    public function addUseImport(FullyQualifiedObjectType $fullyQualifiedObjectType) : void
    {
        /** @var File $file */
        $file = $this->currentFileProvider->getFile();
        $this->useImportTypesInFilePath[$file->getFilePath()][] = $fullyQualifiedObjectType;
    }
    public function addConstantUseImport(FullyQualifiedObjectType $fullyQualifiedObjectType) : void
    {
        /** @var File $file */
        $file = $this->currentFileProvider->getFile();
        $this->constantUseImportTypesInFilePath[$file->getFilePath()][] = $fullyQualifiedObjectType;
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
        $uses = $this->useImportsResolver->resolve();
        foreach ($uses as $use) {
            $prefix = $this->useImportsResolver->resolvePrefix($use);
            foreach ($use->uses as $useUse) {
                if ($useUse->alias instanceof Identifier) {
                    $objectTypes[] = new AliasedObjectType($useUse->alias->toString(), $prefix . $useUse->name);
                } else {
                    $objectTypes[] = new FullyQualifiedObjectType($prefix . $useUse->name);
                }
            }
        }
        return $objectTypes;
    }
    public function hasImport(File $file, FullyQualified $fullyQualified, FullyQualifiedObjectType $fullyQualifiedObjectType) : bool
    {
        $useImports = $this->getUseImportTypesByNode($file, $fullyQualified);
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
        $fileConstantUseImportTypes = $this->constantUseImportTypesInFilePath[$filePath] ?? [];
        foreach ($fileConstantUseImportTypes as $fileConstantUseImportType) {
            // don't compare strtolower for use const as insensitive is allowed, see https://3v4l.org/lteVa
            if ($fileConstantUseImportType->getShortName() === $shortName) {
                return \true;
            }
        }
        $shortName = \strtolower($shortName);
        if ($this->isShortClassImported($filePath, $shortName)) {
            return \true;
        }
        $fileFunctionUseImportTypes = $this->functionUseImportTypesInFilePath[$filePath] ?? [];
        foreach ($fileFunctionUseImportTypes as $fileFunctionUseImportType) {
            if (\strtolower($fileFunctionUseImportType->getShortName()) === $shortName) {
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
        $constantImports = $this->constantUseImportTypesInFilePath[$filePath] ?? [];
        foreach ($constantImports as $constantImport) {
            if ($fullyQualifiedObjectType->equals($constantImport)) {
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
    public function getObjectImportsByFilePath(string $filePath) : array
    {
        return $this->useImportTypesInFilePath[$filePath] ?? [];
    }
    /**
     * @return FullyQualifiedObjectType[]
     */
    public function getConstantImportsByFilePath(string $filePath) : array
    {
        return $this->constantUseImportTypesInFilePath[$filePath] ?? [];
    }
    /**
     * @return FullyQualifiedObjectType[]
     */
    public function getFunctionImportsByFilePath(string $filePath) : array
    {
        return $this->functionUseImportTypesInFilePath[$filePath] ?? [];
    }
    private function isShortClassImported(string $filePath, string $shortName) : bool
    {
        $fileUseImports = $this->useImportTypesInFilePath[$filePath] ?? [];
        foreach ($fileUseImports as $fileUseImport) {
            if (\strtolower($fileUseImport->getShortName()) === $shortName) {
                return \true;
            }
        }
        return \false;
    }
}
