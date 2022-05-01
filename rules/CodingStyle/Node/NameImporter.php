<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Node;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Reflection\ReflectionProvider;
use Rector\CodingStyle\ClassNameImport\AliasUsesResolver;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use RectorPrefix20220501\Symplify\PackageBuilder\Parameter\ParameterProvider;
final class NameImporter
{
    /**
     * @var string[]
     */
    private $aliasedUses = [];
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
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
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
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\CodingStyle\ClassNameImport\AliasUsesResolver $aliasUsesResolver, \Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper $classNameImportSkipper, \RectorPrefix20220501\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\PostRector\Collector\UseNodesToAddCollector $useNodesToAddCollector, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->aliasUsesResolver = $aliasUsesResolver;
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->parameterProvider = $parameterProvider;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @param Use_[] $uses
     */
    public function importName(\PhpParser\Node\Name $name, \Rector\Core\ValueObject\Application\File $file, array $uses) : ?\PhpParser\Node\Name
    {
        if ($this->shouldSkipName($name)) {
            return null;
        }
        if ($this->classNameImportSkipper->isShortNameInUseStatement($name, $uses)) {
            return null;
        }
        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($name);
        if (!$staticType instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType) {
            return null;
        }
        $className = $staticType->getClassName();
        // class has \, no need to search in aliases, mark aliasedUses as empty
        $this->aliasedUses = \strpos($className, '\\') !== \false ? [] : $this->aliasUsesResolver->resolveFromStmts($uses);
        return $this->importNameAndCollectNewUseStatement($file, $name, $staticType, $className);
    }
    private function shouldSkipName(\PhpParser\Node\Name $name) : bool
    {
        $virtualNode = (bool) $name->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::VIRTUAL_NODE, \false);
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
        if (!$this->parameterProvider->provideBoolParameter(\Rector\Core\Configuration\Option::IMPORT_SHORT_CLASSES)) {
            $stringName = $name->toString();
            if (\substr_count($stringName, '\\') === 0) {
                return \true;
            }
        }
        return \false;
    }
    private function importNameAndCollectNewUseStatement(\Rector\Core\ValueObject\Application\File $file, \PhpParser\Node\Name $name, \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType, string $className) : ?\PhpParser\Node\Name
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
    private function isNamespaceOrUseImportName(\PhpParser\Node\Name $name) : bool
    {
        $parentNode = $name->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentNode instanceof \PhpParser\Node\Stmt\Namespace_) {
            return \true;
        }
        return $parentNode instanceof \PhpParser\Node\Stmt\UseUse;
    }
    private function isFunctionOrConstantImportWithSingleName(\PhpParser\Node\Name $name) : bool
    {
        $parentNode = $name->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        $fullName = $name->toString();
        $autoImportNames = $this->parameterProvider->provideBoolParameter(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES);
        if ($autoImportNames && !$parentNode instanceof \PhpParser\Node && \strpos($fullName, '\\') === \false && $this->reflectionProvider->hasFunction(new \PhpParser\Node\Name($fullName), null)) {
            return \true;
        }
        if ($parentNode instanceof \PhpParser\Node\Expr\ConstFetch) {
            return \count($name->parts) === 1;
        }
        if ($parentNode instanceof \PhpParser\Node\Expr\FuncCall) {
            return \count($name->parts) === 1;
        }
        return \false;
    }
    private function addUseImport(\Rector\Core\ValueObject\Application\File $file, \PhpParser\Node\Name $name, \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType) : void
    {
        if ($this->useNodesToAddCollector->hasImport($file, $name, $fullyQualifiedObjectType)) {
            return;
        }
        $parentNode = $name->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentNode instanceof \PhpParser\Node\Expr\FuncCall) {
            $this->useNodesToAddCollector->addFunctionUseImport($fullyQualifiedObjectType);
        } else {
            $this->useNodesToAddCollector->addUseImport($fullyQualifiedObjectType);
        }
    }
}
