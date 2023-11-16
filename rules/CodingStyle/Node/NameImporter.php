<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Node;

use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\SimpleParameterProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class NameImporter
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper
     */
    private $classNameImportSkipper;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\UseNodesToAddCollector
     */
    private $useNodesToAddCollector;
    public function __construct(ClassNameImportSkipper $classNameImportSkipper, StaticTypeMapper $staticTypeMapper, UseNodesToAddCollector $useNodesToAddCollector)
    {
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
    }
    public function importName(FullyQualified $fullyQualified, File $file) : ?Name
    {
        if ($this->shouldSkipName($fullyQualified)) {
            return null;
        }
        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($fullyQualified);
        if (!$staticType instanceof FullyQualifiedObjectType) {
            return null;
        }
        return $this->importNameAndCollectNewUseStatement($file, $fullyQualified, $staticType);
    }
    private function shouldSkipName(FullyQualified $fullyQualified) : bool
    {
        $virtualNode = (bool) $fullyQualified->getAttribute(AttributeKey::VIRTUAL_NODE);
        if ($virtualNode) {
            return \true;
        }
        // is scalar name?
        if (\in_array($fullyQualified->toLowerString(), ['true', 'false', 'bool'], \true)) {
            return \true;
        }
        if ($this->isFunctionOrConstantImportWithSingleName($fullyQualified)) {
            return \true;
        }
        // Importing root namespace classes (like \DateTime) is optional
        if (!SimpleParameterProvider::provideBoolParameter(Option::IMPORT_SHORT_CLASSES)) {
            $stringName = $fullyQualified->toString();
            if (\substr_count($stringName, '\\') === 0) {
                return \true;
            }
        }
        return \false;
    }
    private function importNameAndCollectNewUseStatement(File $file, FullyQualified $fullyQualified, FullyQualifiedObjectType $fullyQualifiedObjectType) : ?Name
    {
        // the same end is already imported → skip
        if ($this->classNameImportSkipper->shouldSkipNameForFullyQualifiedObjectType($file, $fullyQualified, $fullyQualifiedObjectType)) {
            return null;
        }
        if ($this->useNodesToAddCollector->isShortImported($file, $fullyQualifiedObjectType)) {
            if ($this->useNodesToAddCollector->isImportShortable($file, $fullyQualifiedObjectType)) {
                return $fullyQualifiedObjectType->getShortNameNode();
            }
            return null;
        }
        $this->addUseImport($file, $fullyQualified, $fullyQualifiedObjectType);
        return $fullyQualifiedObjectType->getShortNameNode();
    }
    private function isFunctionOrConstantImportWithSingleName(FullyQualified $fullyQualified) : bool
    {
        if ($fullyQualified->getAttribute(AttributeKey::IS_CONSTFETCH_NAME) === \true) {
            return \count($fullyQualified->getParts()) === 1;
        }
        if ($fullyQualified->getAttribute(AttributeKey::IS_FUNCCALL_NAME) === \true) {
            return \count($fullyQualified->getParts()) === 1;
        }
        return \false;
    }
    private function addUseImport(File $file, FullyQualified $fullyQualified, FullyQualifiedObjectType $fullyQualifiedObjectType) : void
    {
        if ($this->useNodesToAddCollector->hasImport($file, $fullyQualified, $fullyQualifiedObjectType)) {
            return;
        }
        if ($fullyQualified->getAttribute(AttributeKey::IS_FUNCCALL_NAME) === \true) {
            $this->useNodesToAddCollector->addFunctionUseImport($fullyQualifiedObjectType);
        } elseif ($fullyQualified->getAttribute(AttributeKey::IS_CONSTFETCH_NAME) === \true) {
            $this->useNodesToAddCollector->addConstantUseImport($fullyQualifiedObjectType);
        } else {
            $this->useNodesToAddCollector->addUseImport($fullyQualifiedObjectType);
        }
    }
}
