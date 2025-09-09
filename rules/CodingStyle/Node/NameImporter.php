<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Node;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\Naming\Naming\AliasNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\PhpParser\FullyQualifiedNodeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\ValueObject\Application\File;
final class NameImporter
{
    /**
     * @readonly
     */
    private ClassNameImportSkipper $classNameImportSkipper;
    /**
     * @readonly
     */
    private FullyQualifiedNodeMapper $fullyQualifiedNodeMapper;
    /**
     * @readonly
     */
    private UseNodesToAddCollector $useNodesToAddCollector;
    /**
     * @readonly
     */
    private AliasNameResolver $aliasNameResolver;
    public function __construct(ClassNameImportSkipper $classNameImportSkipper, FullyQualifiedNodeMapper $fullyQualifiedNodeMapper, UseNodesToAddCollector $useNodesToAddCollector, AliasNameResolver $aliasNameResolver)
    {
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->fullyQualifiedNodeMapper = $fullyQualifiedNodeMapper;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
        $this->aliasNameResolver = $aliasNameResolver;
    }
    /**
     * @param array<Use_|GroupUse> $currentUses
     */
    public function importName(FullyQualified $fullyQualified, File $file, array $currentUses): ?Name
    {
        if ($this->classNameImportSkipper->shouldSkipName($fullyQualified, $currentUses)) {
            return null;
        }
        $staticType = $this->fullyQualifiedNodeMapper->mapToPHPStan($fullyQualified);
        if (!$staticType instanceof FullyQualifiedObjectType) {
            return null;
        }
        return $this->importNameAndCollectNewUseStatement($file, $fullyQualified, $staticType, $currentUses);
    }
    /**
     * @param array<Use_|GroupUse> $currentUses
     */
    private function resolveNameInUse(FullyQualified $fullyQualified, array $currentUses): ?Name
    {
        $aliasName = $this->aliasNameResolver->resolveByName($fullyQualified, $currentUses);
        if (is_string($aliasName)) {
            return new Name($aliasName);
        }
        if (substr_count($fullyQualified->toCodeString(), '\\') === 1) {
            return null;
        }
        $lastName = $fullyQualified->getLast();
        foreach ($currentUses as $currentUse) {
            foreach ($currentUse->uses as $useUse) {
                if ($useUse->name->getLast() !== $lastName) {
                    continue;
                }
                if ($useUse->alias instanceof Identifier && $useUse->alias->toString() !== $lastName) {
                    return new Name($lastName);
                }
            }
        }
        return null;
    }
    /**
     * @param array<Use_|GroupUse> $currentUses
     */
    private function importNameAndCollectNewUseStatement(File $file, FullyQualified $fullyQualified, FullyQualifiedObjectType $fullyQualifiedObjectType, array $currentUses): ?Name
    {
        // make use of existing use import
        $nameInUse = $this->resolveNameInUse($fullyQualified, $currentUses);
        if ($nameInUse instanceof Name) {
            $nameInUse->setAttribute(AttributeKey::NAMESPACED_NAME, $fullyQualified->toString());
            return $nameInUse;
        }
        $nameInNamespacedScope = $this->resolveNameInNamespacedScope($fullyQualified);
        if ($nameInNamespacedScope instanceof Name) {
            $nameInNamespacedScope->setAttribute(AttributeKey::NAMESPACED_NAME, $fullyQualified->toString());
            return $nameInNamespacedScope;
        }
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
    private function resolveNameInNamespacedScope(FullyQualified $fullyQualified): ?Name
    {
        /**
         * Note: Don't use ScopeFetcher::fetch() on Name instance,
         * Scope can be null on Name
         * This is part of ScopeAnalyzer::NON_REFRESHABLE_NODES
         * @see https://github.com/rectorphp/rector-src/blob/9929af7c0179929b4fde6915cb7a06c3141dde6c/src/NodeAnalyzer/ScopeAnalyzer.php#L17
         */
        $scope = $fullyQualified->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return null;
        }
        $namespace = $scope->getNamespace();
        if ($namespace === null) {
            return null;
        }
        $shortName = $fullyQualified->getLast();
        $namepaceFullyQualifiedName = substr($fullyQualified->toString(), 0, -strlen($shortName) - 1);
        if ($namepaceFullyQualifiedName === $namespace) {
            return new Name($shortName);
        }
        return null;
    }
    private function addUseImport(File $file, FullyQualified $fullyQualified, FullyQualifiedObjectType $fullyQualifiedObjectType): void
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
