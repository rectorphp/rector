<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Node;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\UseUse;
use Rector\CodingStyle\ClassNameImport\AliasUsesResolver;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\Core\Configuration\Option;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\PSR4\Collector\RenamedClassesCollector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class NameImporter
{
    /**
     * @var string[]
     */
    private $aliasedUses = [];

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var AliasUsesResolver
     */
    private $aliasUsesResolver;

    /**
     * @var ClassNameImportSkipper
     */
    private $classNameImportSkipper;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var UseNodesToAddCollector
     */
    private $useNodesToAddCollector;

    /**
     * @var RenamedClassesCollector
     */
    private $renamedClassesCollector;

    public function __construct(
        AliasUsesResolver $aliasUsesResolver,
        ClassNameImportSkipper $classNameImportSkipper,
        NodeNameResolver $nodeNameResolver,
        ParameterProvider $parameterProvider,
        RenamedClassesCollector $renamedClassesCollector,
        StaticTypeMapper $staticTypeMapper,
        UseNodesToAddCollector $useNodesToAddCollector
    ) {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->aliasUsesResolver = $aliasUsesResolver;
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->parameterProvider = $parameterProvider;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
        $this->renamedClassesCollector = $renamedClassesCollector;
    }

    public function importName(Name $name): ?Name
    {
        if ($this->shouldSkipName($name)) {
            return null;
        }

        if ($this->classNameImportSkipper->isShortNameInUseStatement($name)) {
            return null;
        }

        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($name);
        if (! $staticType instanceof FullyQualifiedObjectType) {
            return null;
        }

        $this->aliasedUses = $this->aliasUsesResolver->resolveForNode($name);

        return $this->importNameAndCollectNewUseStatement($name, $staticType);
    }

    private function shouldSkipName(Name $name): bool
    {
        $virtualNode = $name->getAttribute(AttributeKey::VIRTUAL_NODE);
        if ($virtualNode) {
            return true;
        }

        // is scalar name?
        if (in_array($name->toLowerString(), ['true', 'false', 'bool'], true)) {
            return true;
        }

        // namespace <name>
        // use <name>;
        if ($this->isNamespaceOrUseImportName($name)) {
            return true;
        }

        if ($this->isFunctionOrConstantImportWithSingleName($name)) {
            return true;
        }

        if ($this->isNonExistingClassLikeAndFunctionFullyQualifiedName($name)) {
            return true;
        }

        // Importing root namespace classes (like \DateTime) is optional
        $importShortClasses = $this->parameterProvider->provideParameter(Option::IMPORT_SHORT_CLASSES);
        if (! $importShortClasses) {
            $name = $this->nodeNameResolver->getName($name);
            if ($name !== null && substr_count($name, '\\') === 0) {
                return true;
            }
        }

        return false;
    }

    private function importNameAndCollectNewUseStatement(
        Name $name,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): ?Name {
        // the same end is already imported → skip
        if ($this->classNameImportSkipper->shouldSkipNameForFullyQualifiedObjectType(
            $name,
            $fullyQualifiedObjectType
        )) {
            return null;
        }

        if ($this->useNodesToAddCollector->isShortImported($name, $fullyQualifiedObjectType)) {
            if ($this->useNodesToAddCollector->isImportShortable($name, $fullyQualifiedObjectType)) {
                return $fullyQualifiedObjectType->getShortNameNode();
            }

            return null;
        }

        $this->addUseImport($name, $fullyQualifiedObjectType);

        // possibly aliased
        foreach ($this->aliasedUses as $aliasUse) {
            if ($fullyQualifiedObjectType->getClassName() === $aliasUse) {
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
    private function isNamespaceOrUseImportName(Name $name): bool
    {
        $parentNode = $name->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Namespace_) {
            return true;
        }

        return $parentNode instanceof UseUse;
    }

    private function isFunctionOrConstantImportWithSingleName(Name $name): bool
    {
        $parentNode = $name->getAttribute(AttributeKey::PARENT_NODE);

        $fullName = $name->toString();
        $autoImportNames = $this->parameterProvider->provideParameter(Option::AUTO_IMPORT_NAMES);
        if ($autoImportNames && ! $parentNode instanceof Node && ! Strings::contains(
            $fullName,
            '\\'
        ) && function_exists($fullName)) {
            return true;
        }

        if (! $parentNode instanceof ConstFetch && ! $parentNode instanceof FuncCall) {
            return false;
        }

        return count($name->parts) === 1;
    }

    private function isNonExistingClassLikeAndFunctionFullyQualifiedName(Name $name): bool
    {
        if (! $name instanceof FullyQualified) {
            return false;
        }

        // can be also in to be renamed classes
        $classOrFunctionName = $name->toString();

        $oldToNewClasses = $this->renamedClassesCollector->getOldToNewClasses();
        if (in_array($classOrFunctionName, $oldToNewClasses, true)) {
            return false;
        }

        // skip-non existing class-likes and functions
        if (ClassExistenceStaticHelper::doesClassLikeExist($classOrFunctionName)) {
            return false;
        }

        return ! function_exists($classOrFunctionName);
    }

    private function addUseImport(Name $name, FullyQualifiedObjectType $fullyQualifiedObjectType): void
    {
        if ($this->useNodesToAddCollector->hasImport($name, $fullyQualifiedObjectType)) {
            return;
        }

        $parentNode = $name->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof FuncCall) {
            $this->useNodesToAddCollector->addFunctionUseImport($name, $fullyQualifiedObjectType);
        } else {
            $this->useNodesToAddCollector->addUseImport($name, $fullyQualifiedObjectType);
        }
    }
}
