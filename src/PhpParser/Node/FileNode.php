<?php

declare (strict_types=1);
namespace Rector\PhpParser\Node;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Use_;
use Rector\CodingStyle\ClassNameImport\ValueObject\PendingImports;
use Rector\CodingStyle\ClassNameImport\ValueObject\UsedImports;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
/**
 * Inspired by https://github.com/phpstan/phpstan-src/commit/ed81c3ad0b9877e6122c79b4afda9d10f3994092
 */
class FileNode extends Stmt
{
    /**
     * @var Stmt[]
     */
    public array $stmts;
    /**
     * @var UsedImports
     */
    private UsedImports $usedImports;
    /**
     * Imports queued to be added on the next UseAddingPostRector run; scoped to this file
     * @readonly
     */
    private PendingImports $pendingImports;
    /**
     * @param Stmt[] $stmts
     * @param UsedImports $usedImports Resolved once on file parse, then kept in sync incrementally as imports are added
     */
    public function __construct(array $stmts, UsedImports $usedImports)
    {
        $this->stmts = $stmts;
        $this->usedImports = $usedImports;
        $this->pendingImports = new PendingImports();
        $firstStmt = $stmts[0] ?? null;
        $attributes = $firstStmt instanceof Node ? $firstStmt->getAttributes() : [];
        parent::__construct($attributes);
    }
    /**
     * Adds new use imports into the file/namespace and keeps the tracked used imports in sync,
     * so the next traversal iteration converges without re-resolving.
     *
     * @param array<FullyQualifiedObjectType|AliasedObjectType> $useImportTypes
     * @param FullyQualifiedObjectType[] $constantUseImportTypes
     * @param FullyQualifiedObjectType[] $functionUseImportTypes
     */
    public function addImports(array $useImportTypes, array $constantUseImportTypes, array $functionUseImportTypes): bool
    {
        $namespace = $this->resolvePlacementNamespace();
        $placementNode = $namespace ?? $this;
        $namespaceName = $namespace instanceof Namespace_ && $namespace->name instanceof Name ? $namespace->name->toString() : null;
        // no namespace? only keep namespaced names at the file top
        if (!$namespace instanceof Namespace_) {
            $useImportTypes = $this->filterOutNonNamespacedNames($useImportTypes);
        }
        $useImportTypes = $this->diffFullyQualifiedObjectTypes($useImportTypes, $this->usedImports->getUseImports());
        $constantUseImportTypes = $this->diffFullyQualifiedObjectTypes($constantUseImportTypes, $this->usedImports->getConstantImports());
        $functionUseImportTypes = $this->diffFullyQualifiedObjectTypes($functionUseImportTypes, $this->usedImports->getFunctionImports());
        $newUses = $this->createUses($useImportTypes, $constantUseImportTypes, $functionUseImportTypes, $namespaceName);
        if ($newUses === []) {
            return \false;
        }
        // remove empty use stmts
        $placementNode->stmts = array_values(array_filter($placementNode->stmts, static function (Stmt $stmt): bool {
            if (!$stmt instanceof Use_) {
                return \true;
            }
            return $stmt->uses !== [];
        }));
        // place after declare strict_types
        foreach ($placementNode->stmts as $key => $stmt) {
            // maybe just added a space
            if ($stmt instanceof Nop) {
                continue;
            }
            // when we found a non-declare, directly stop
            if (!$stmt instanceof Declare_) {
                break;
            }
            $nodesToAdd = array_merge([new Nop()], $newUses);
            $this->mirrorUseComments($placementNode->stmts, $newUses, $key + 1);
            // remove space before next use tweak
            $nextStmt = $placementNode->stmts[$key + 1] ?? null;
            if ($nextStmt instanceof Use_ || $nextStmt instanceof GroupUse) {
                $nextStmt->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            }
            array_splice($placementNode->stmts, $key + 1, 0, $nodesToAdd);
            $this->appendUsedImports($useImportTypes, $functionUseImportTypes, $constantUseImportTypes);
            return \true;
        }
        $this->mirrorUseComments($placementNode->stmts, $newUses);
        // make use stmts first
        $placementNode->stmts = array_merge($newUses, $this->resolveInsertNop($placementNode), $placementNode->stmts);
        $placementNode->stmts = array_values($placementNode->stmts);
        $this->appendUsedImports($useImportTypes, $functionUseImportTypes, $constantUseImportTypes);
        return \true;
    }
    public function getPendingImports(): PendingImports
    {
        return $this->pendingImports;
    }
    public function hasImport(FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $found = \false;
        foreach ($this->resolveUsedImportTypes() as $useImport) {
            if ($useImport->equals($fullyQualifiedObjectType)) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    /**
     * The queued use imports merged with the use imports already present in the file
     *
     * @return array<AliasedObjectType|FullyQualifiedObjectType>
     */
    public function resolveUsedImportTypes(): array
    {
        $objectTypes = $this->pendingImports->getUseImports();
        foreach ($this->getUsesAndGroupUses() as $use) {
            $prefix = $use instanceof GroupUse ? $use->prefix . '\\' : '';
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
    /**
     * Removes the given use imports from the file/namespace
     *
     * @param string[] $removedUses
     */
    public function removeImports(array $removedUses): bool
    {
        $node = $this->resolvePlacementNamespace() ?? $this;
        $hasRemoved = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Use_) {
                continue;
            }
            if ($this->removeUseFromUse($removedUses, $stmt)) {
                $hasRemoved = \true;
            }
            // remove empty uses
            if ($stmt->uses === []) {
                unset($node->stmts[$key]);
            }
        }
        if ($hasRemoved) {
            $node->stmts = array_values($node->stmts);
        }
        return $hasRemoved;
    }
    /**
     * This triggers Printed method with "pFileNode" name
     * @see \Rector\PhpParser\Printer\BetterStandardPrinter::pStmt_FileNode()
     */
    public function getType(): string
    {
        return 'Stmt_FileNode';
    }
    /**
     * @return array<int, string>
     */
    public function getSubNodeNames(): array
    {
        return ['stmts'];
    }
    public function isNamespaced(): bool
    {
        $found = \false;
        foreach ($this->stmts as $stmt) {
            if ($stmt instanceof Namespace_) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    public function getNamespace(): ?Namespace_
    {
        /** @var Namespace_[] $namespaces */
        $namespaces = array_filter($this->stmts, static fn(Stmt $stmt): bool => $stmt instanceof Namespace_);
        if (count($namespaces) === 1) {
            return current($namespaces);
        }
        return null;
    }
    /**
     * @return array<Use_|GroupUse>
     */
    public function getUsesAndGroupUses(): array
    {
        $rootNode = $this->getNamespace();
        if (!$rootNode instanceof Namespace_) {
            $rootNode = $this;
        }
        return array_filter($rootNode->stmts, static fn(Stmt $stmt): bool => $stmt instanceof Use_ || $stmt instanceof GroupUse);
    }
    /**
     * @return Use_[]
     */
    public function getUses(): array
    {
        $rootNode = $this->getNamespace();
        if (!$rootNode instanceof Namespace_) {
            $rootNode = $this;
        }
        return array_filter($rootNode->stmts, static fn(Stmt $stmt): bool => $stmt instanceof Use_);
    }
    /**
     * Mirrors the legacy placement target: the first namespace if any, else the file root itself
     */
    private function resolvePlacementNamespace(): ?Namespace_
    {
        foreach ($this->stmts as $stmt) {
            if ($stmt instanceof Namespace_) {
                return $stmt;
            }
        }
        return null;
    }
    /**
     * @param array<FullyQualifiedObjectType|AliasedObjectType> $useImportTypes
     * @return array<FullyQualifiedObjectType|AliasedObjectType>
     */
    private function filterOutNonNamespacedNames(array $useImportTypes): array
    {
        $namespacedUseImportTypes = [];
        foreach ($useImportTypes as $useImportType) {
            if (strpos($useImportType->getClassName(), '\\') === \false) {
                continue;
            }
            $namespacedUseImportTypes[] = $useImportType;
        }
        return $namespacedUseImportTypes;
    }
    /**
     * @param array<FullyQualifiedObjectType|AliasedObjectType> $useImportTypes
     * @param FullyQualifiedObjectType[] $functionUseImportTypes
     * @param FullyQualifiedObjectType[] $constantUseImportTypes
     */
    private function appendUsedImports(array $useImportTypes, array $functionUseImportTypes, array $constantUseImportTypes): void
    {
        $this->usedImports = new UsedImports(array_merge($this->usedImports->getUseImports(), $useImportTypes), array_merge($this->usedImports->getFunctionImports(), $functionUseImportTypes), array_merge($this->usedImports->getConstantImports(), $constantUseImportTypes));
    }
    /**
     * @template TObjectType of FullyQualifiedObjectType|AliasedObjectType
     * @param array<TObjectType> $mainTypes
     * @param array<FullyQualifiedObjectType|AliasedObjectType> $typesToRemove
     * @return list<TObjectType>
     */
    private function diffFullyQualifiedObjectTypes(array $mainTypes, array $typesToRemove): array
    {
        foreach ($mainTypes as $key => $mainType) {
            foreach ($typesToRemove as $typeToRemove) {
                if ($mainType->equals($typeToRemove)) {
                    unset($mainTypes[$key]);
                }
            }
        }
        return array_values($mainTypes);
    }
    /**
     * @param array<AliasedObjectType|FullyQualifiedObjectType> $useImportTypes
     * @param array<FullyQualifiedObjectType|AliasedObjectType> $constantUseImportTypes
     * @param array<FullyQualifiedObjectType|AliasedObjectType> $functionUseImportTypes
     * @return Use_[]
     */
    private function createUses(array $useImportTypes, array $constantUseImportTypes, array $functionUseImportTypes, ?string $namespaceName): array
    {
        $newUses = [];
        /** @var array<Use_::TYPE_*, array<AliasedObjectType|FullyQualifiedObjectType>> $importsMapping */
        $importsMapping = [Use_::TYPE_NORMAL => $useImportTypes, Use_::TYPE_CONSTANT => $constantUseImportTypes, Use_::TYPE_FUNCTION => $functionUseImportTypes];
        foreach ($importsMapping as $type => $importTypes) {
            /** @var AliasedObjectType|FullyQualifiedObjectType $importType */
            foreach ($importTypes as $importType) {
                if ($namespaceName !== null && $this->isCurrentNamespace($namespaceName, $importType)) {
                    continue;
                }
                if ($namespaceName === null && $importType instanceof FullyQualifiedObjectType && substr_count(ltrim($importType->getClassName(), '\\'), '\\') === 0) {
                    continue;
                }
                $newUses[] = $importType->getUseNode($type);
            }
        }
        return $newUses;
    }
    /**
     * @param \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType|\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $objectType
     */
    private function isCurrentNamespace(string $namespaceName, $objectType): bool
    {
        $className = $objectType->getClassName();
        if (strncmp($className, $namespaceName . '\\', strlen($namespaceName . '\\')) !== 0) {
            return \false;
        }
        return $namespaceName . '\\' . $objectType->getShortName() === $className;
    }
    /**
     * @return Nop[]
     * @param $this|\PhpParser\Node\Stmt\Namespace_ $node
     */
    private function resolveInsertNop($node): array
    {
        $currentStmt = $node->stmts[0] ?? null;
        if (!$currentStmt instanceof Stmt || $currentStmt instanceof Use_ || $currentStmt instanceof GroupUse) {
            return [];
        }
        return [new Nop()];
    }
    /**
     * @param Stmt[] $stmts
     * @param Use_[] $newUses
     */
    private function mirrorUseComments(array $stmts, array $newUses, int $indexStmt = 0): void
    {
        if ($stmts === []) {
            return;
        }
        if (isset($stmts[$indexStmt]) && $stmts[$indexStmt] instanceof Use_) {
            $comments = (array) $stmts[$indexStmt]->getAttribute(AttributeKey::COMMENTS);
            if ($comments !== []) {
                $newUses[0]->setAttribute(AttributeKey::COMMENTS, $stmts[$indexStmt]->getAttribute(AttributeKey::COMMENTS));
                $stmts[$indexStmt]->setAttribute(AttributeKey::COMMENTS, []);
            }
        }
    }
    /**
     * @param string[] $removedUses
     */
    private function removeUseFromUse(array $removedUses, Use_ $use): bool
    {
        $hasChanged = \false;
        foreach ($use->uses as $usesKey => $useUse) {
            $useName = $useUse->name->toString();
            if (!in_array($useName, $removedUses, \true)) {
                continue;
            }
            unset($use->uses[$usesKey]);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            $use->uses = array_values($use->uses);
        }
        return $hasChanged;
    }
}
