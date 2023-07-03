<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Node;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use Rector\CodingStyle\ClassNameImport\AliasUsesResolver;
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
     * @var \Rector\CodingStyle\ClassNameImport\AliasUsesResolver
     */
    private $aliasUsesResolver;
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
    /**
     * @var string[]
     */
    private $aliasedUses = [];
    public function __construct(AliasUsesResolver $aliasUsesResolver, ClassNameImportSkipper $classNameImportSkipper, StaticTypeMapper $staticTypeMapper, UseNodesToAddCollector $useNodesToAddCollector)
    {
        $this->aliasUsesResolver = $aliasUsesResolver;
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
    }
    /**
     * @param Use_[]|GroupUse[] $uses
     */
    public function importName(Name $name, File $file, array $uses) : ?Name
    {
        if ($this->shouldSkipName($name)) {
            return null;
        }
        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($name);
        if (!$staticType instanceof FullyQualifiedObjectType) {
            return null;
        }
        $className = $staticType->getClassName();
        // class has \, no need to search in aliases, mark aliasedUses as empty
        $this->aliasedUses = \strpos($className, '\\') !== \false ? [] : $this->aliasUsesResolver->resolveFromStmts($uses);
        return $this->importNameAndCollectNewUseStatement($file, $name, $staticType, $className);
    }
    private function shouldSkipName(Name $name) : bool
    {
        $virtualNode = (bool) $name->getAttribute(AttributeKey::VIRTUAL_NODE);
        if ($virtualNode) {
            return \true;
        }
        // is scalar name?
        if (\in_array($name->toLowerString(), ['true', 'false', 'bool'], \true)) {
            return \true;
        }
        // namespace <name>
        // use <name>;
        if ($this->isNamespaceOrUseImportName($name)) {
            return \true;
        }
        if ($this->isFunctionOrConstantImportWithSingleName($name)) {
            return \true;
        }
        // Importing root namespace classes (like \DateTime) is optional
        if (!SimpleParameterProvider::provideBoolParameter(Option::IMPORT_SHORT_CLASSES)) {
            $stringName = $name->toString();
            if (\substr_count($stringName, '\\') === 0) {
                return \true;
            }
        }
        return \false;
    }
    private function importNameAndCollectNewUseStatement(File $file, Name $name, FullyQualifiedObjectType $fullyQualifiedObjectType, string $className) : ?Name
    {
        // the same end is already imported → skip
        if ($this->classNameImportSkipper->shouldSkipNameForFullyQualifiedObjectType($file, $name, $fullyQualifiedObjectType)) {
            return null;
        }
        if ($this->useNodesToAddCollector->isShortImported($file, $fullyQualifiedObjectType)) {
            if ($this->useNodesToAddCollector->isImportShortable($file, $fullyQualifiedObjectType)) {
                return $fullyQualifiedObjectType->getShortNameNode();
            }
            return null;
        }
        $this->addUseImport($file, $name, $fullyQualifiedObjectType);
        if ($this->aliasedUses === []) {
            return $fullyQualifiedObjectType->getShortNameNode();
        }
        // possibly aliased
        foreach ($this->aliasedUses as $aliasedUse) {
            if ($className === $aliasedUse) {
                return null;
            }
        }
        return $fullyQualifiedObjectType->getShortNameNode();
    }
    /**
     * Skip:
     * - namespace name
     * - use import name
     */
    private function isNamespaceOrUseImportName(Name $name) : bool
    {
        if ($name->getAttribute(AttributeKey::IS_NAMESPACE_NAME) === \true) {
            return \true;
        }
        return $name->getAttribute(AttributeKey::IS_USEUSE_NAME) === \true;
    }
    private function isFunctionOrConstantImportWithSingleName(Name $name) : bool
    {
        if ($name->getAttribute(AttributeKey::IS_CONSTFETCH_NAME) === \true) {
            return \count($name->getParts()) === 1;
        }
        if ($name->getAttribute(AttributeKey::IS_FUNCCALL_NAME) === \true) {
            return \count($name->getParts()) === 1;
        }
        return \false;
    }
    private function addUseImport(File $file, Name $name, FullyQualifiedObjectType $fullyQualifiedObjectType) : void
    {
        if ($this->useNodesToAddCollector->hasImport($file, $name, $fullyQualifiedObjectType)) {
            return;
        }
        if ($name->getAttribute(AttributeKey::IS_FUNCCALL_NAME) === \true) {
            $this->useNodesToAddCollector->addFunctionUseImport($fullyQualifiedObjectType);
        } else {
            $this->useNodesToAddCollector->addUseImport($fullyQualifiedObjectType);
        }
    }
}
