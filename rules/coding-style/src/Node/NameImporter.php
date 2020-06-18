<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Node;

use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\UseUse;
use Rector\CodingStyle\Imports\AliasUsesResolver;
use Rector\CodingStyle\Imports\ImportSkipper;
use Rector\Core\Configuration\Option;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\StaticTypeMapper;
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
     * @var ImportSkipper
     */
    private $importSkipper;

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

    public function __construct(
        StaticTypeMapper $staticTypeMapper,
        AliasUsesResolver $aliasUsesResolver,
        ImportSkipper $importSkipper,
        NodeNameResolver $nodeNameResolver,
        ParameterProvider $parameterProvider,
        UseNodesToAddCollector $useNodesToAddCollector
    ) {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->aliasUsesResolver = $aliasUsesResolver;
        $this->importSkipper = $importSkipper;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->parameterProvider = $parameterProvider;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
    }

    public function importName(Name $name): ?Name
    {
        if ($this->shouldSkipName($name)) {
            return null;
        }

        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($name);

        // propagate to fqn, that's what we need here
        if ($staticType instanceof AliasedObjectType) {
            $staticType = new FullyQualifiedObjectType($staticType->getFullyQualifiedClass());
        }

        if (! $staticType instanceof FullyQualifiedObjectType) {
            return null;
        }

        $this->aliasedUses = $this->aliasUsesResolver->resolveForNode($name);

        return $this->importNameAndCollectNewUseStatement($name, $staticType);
    }

    private function shouldSkipName(Name $name): bool
    {
        if ($name->getAttribute(AttributeKey::VIRTUAL_NODE)) {
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
        $importShortClasses = $this->parameterProvider->provideParameter(Option::IMPORT_SHORT_CLASSES_PARAMETER);
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
        if ($this->importSkipper->shouldSkipNameForFullyQualifiedObjectType($name, $fullyQualifiedObjectType)) {
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

        // skip-non existing class-likes and functions
        if (ClassExistenceStaticHelper::doesClassLikeExist($name->toString())) {
            return false;
        }

        return ! function_exists($name->toString());
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
