<?php

declare(strict_types=1);

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
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class NameImporter
{
    /**
     * @var string[]
     */
    private array $aliasedUses = [];

    public function __construct(
        private AliasUsesResolver $aliasUsesResolver,
        private ClassNameImportSkipper $classNameImportSkipper,
        private NodeNameResolver $nodeNameResolver,
        private ParameterProvider $parameterProvider,
        private StaticTypeMapper $staticTypeMapper,
        private UseNodesToAddCollector $useNodesToAddCollector,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    /**
     * @param Use_[] $uses
     */
    public function importName(Name $name, File $file, array $uses): ?Name
    {
        if ($this->shouldSkipName($name)) {
            return null;
        }

        if ($this->classNameImportSkipper->isShortNameInUseStatement($name, $uses)) {
            return null;
        }

        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($name);
        if (! $staticType instanceof FullyQualifiedObjectType) {
            return null;
        }

        $className = $staticType->getClassName();
        // class has \, no need to search in aliases, mark aliasedUses as empty
        $this->aliasedUses = str_contains($className, '\\')
            ? []
            : $this->aliasUsesResolver->resolveFromStmts($uses);

        return $this->importNameAndCollectNewUseStatement($file, $name, $staticType, $className);
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

        // Importing root namespace classes (like \DateTime) is optional
        if (! $this->parameterProvider->provideBoolParameter(Option::IMPORT_SHORT_CLASSES)) {
            $stringName = $this->nodeNameResolver->getName($name);
            if ($stringName !== null && substr_count($stringName, '\\') === 0) {
                return true;
            }
        }

        return false;
    }

    private function importNameAndCollectNewUseStatement(
        File $file,
        Name $name,
        FullyQualifiedObjectType $fullyQualifiedObjectType,
        string $className
    ): ?Name {
        // the same end is already imported → skip
        if ($this->classNameImportSkipper->shouldSkipNameForFullyQualifiedObjectType(
            $file,
            $name,
            $fullyQualifiedObjectType
        )) {
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

        $autoImportNames = $this->parameterProvider->provideBoolParameter(Option::AUTO_IMPORT_NAMES);
        if ($autoImportNames && ! $parentNode instanceof Node && ! \str_contains(
            $fullName,
            '\\'
        ) && $this->reflectionProvider->hasFunction(new Name($fullName), null)) {
            return true;
        }

        if ($parentNode instanceof ConstFetch) {
            return count($name->parts) === 1;
        }

        if ($parentNode instanceof FuncCall) {
            return count($name->parts) === 1;
        }

        return false;
    }

    private function addUseImport(File $file, Name $name, FullyQualifiedObjectType $fullyQualifiedObjectType): void
    {
        if ($this->useNodesToAddCollector->hasImport($file, $name, $fullyQualifiedObjectType)) {
            return;
        }

        $parentNode = $name->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof FuncCall) {
            $this->useNodesToAddCollector->addFunctionUseImport($fullyQualifiedObjectType);
        } else {
            $this->useNodesToAddCollector->addUseImport($fullyQualifiedObjectType);
        }
    }
}
