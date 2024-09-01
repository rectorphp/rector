<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Application;

use RectorPrefix202409\Nette\Utils\Strings;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Type\ObjectType;
use Rector\CodingStyle\ClassNameImport\UsedImportsResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class UseImportsAdder
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\UsedImportsResolver
     */
    private $usedImportsResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    public function __construct(UsedImportsResolver $usedImportsResolver, TypeFactory $typeFactory)
    {
        $this->usedImportsResolver = $usedImportsResolver;
        $this->typeFactory = $typeFactory;
    }
    /**
     * @param Stmt[] $stmts
     * @param array<FullyQualifiedObjectType|AliasedObjectType> $useImportTypes
     * @param array<FullyQualifiedObjectType|AliasedObjectType> $constantUseImportTypes
     * @param array<FullyQualifiedObjectType|AliasedObjectType> $functionUseImportTypes
     * @return Stmt[]
     */
    public function addImportsToStmts(FileWithoutNamespace $fileWithoutNamespace, array $stmts, array $useImportTypes, array $constantUseImportTypes, array $functionUseImportTypes) : array
    {
        $usedImports = $this->usedImportsResolver->resolveForStmts($stmts);
        $existingUseImportTypes = $usedImports->getUseImports();
        $existingConstantUseImports = $usedImports->getConstantImports();
        $existingFunctionUseImports = $usedImports->getFunctionImports();
        $useImportTypes = $this->diffFullyQualifiedObjectTypes($useImportTypes, $existingUseImportTypes);
        $constantUseImportTypes = $this->diffFullyQualifiedObjectTypes($constantUseImportTypes, $existingConstantUseImports);
        $functionUseImportTypes = $this->diffFullyQualifiedObjectTypes($functionUseImportTypes, $existingFunctionUseImports);
        $newUses = $this->createUses($useImportTypes, $constantUseImportTypes, $functionUseImportTypes, null);
        if ($newUses === []) {
            return [$fileWithoutNamespace];
        }
        // place after declare strict_types
        foreach ($stmts as $key => $stmt) {
            // maybe just added a space
            if ($stmt instanceof Nop) {
                continue;
            }
            // when we found a non-declare, directly stop
            if (!$stmt instanceof Declare_) {
                break;
            }
            $nodesToAdd = \array_merge([new Nop()], $newUses);
            $this->mirrorUseComments($stmts, $newUses, $key + 1);
            // remove space before next use tweak
            if (isset($stmts[$key + 1]) && ($stmts[$key + 1] instanceof Use_ || $stmts[$key + 1] instanceof GroupUse)) {
                $stmts[$key + 1]->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            }
            \array_splice($stmts, $key + 1, 0, $nodesToAdd);
            $fileWithoutNamespace->stmts = $stmts;
            $fileWithoutNamespace->stmts = \array_values($fileWithoutNamespace->stmts);
            return [$fileWithoutNamespace];
        }
        $this->mirrorUseComments($stmts, $newUses);
        // make use stmts first
        $fileWithoutNamespace->stmts = \array_merge($newUses, $this->resolveInsertNop($fileWithoutNamespace), $stmts);
        $fileWithoutNamespace->stmts = \array_values($fileWithoutNamespace->stmts);
        return [$fileWithoutNamespace];
    }
    /**
     * @param FullyQualifiedObjectType[] $useImportTypes
     * @param FullyQualifiedObjectType[] $constantUseImportTypes
     * @param FullyQualifiedObjectType[] $functionUseImportTypes
     */
    public function addImportsToNamespace(Namespace_ $namespace, array $useImportTypes, array $constantUseImportTypes, array $functionUseImportTypes) : void
    {
        $namespaceName = $this->getNamespaceName($namespace);
        $existingUsedImports = $this->usedImportsResolver->resolveForStmts($namespace->stmts);
        $existingUseImportTypes = $existingUsedImports->getUseImports();
        $existingConstantUseImportTypes = $existingUsedImports->getConstantImports();
        $existingFunctionUseImportTypes = $existingUsedImports->getFunctionImports();
        $existingUseImportTypes = $this->typeFactory->uniquateTypes($existingUseImportTypes);
        $useImportTypes = $this->diffFullyQualifiedObjectTypes($useImportTypes, $existingUseImportTypes);
        $constantUseImportTypes = $this->diffFullyQualifiedObjectTypes($constantUseImportTypes, $existingConstantUseImportTypes);
        $functionUseImportTypes = $this->diffFullyQualifiedObjectTypes($functionUseImportTypes, $existingFunctionUseImportTypes);
        $newUses = $this->createUses($useImportTypes, $constantUseImportTypes, $functionUseImportTypes, $namespaceName);
        if ($newUses === []) {
            return;
        }
        $this->mirrorUseComments($namespace->stmts, $newUses);
        $namespace->stmts = \array_merge($newUses, $this->resolveInsertNop($namespace), $namespace->stmts);
        $namespace->stmts = \array_values($namespace->stmts);
    }
    /**
     * @return Nop[]
     * @param \Rector\PhpParser\Node\CustomNode\FileWithoutNamespace|\PhpParser\Node\Stmt\Namespace_ $namespace
     */
    private function resolveInsertNop($namespace) : array
    {
        $currentStmt = $namespace->stmts[0] ?? null;
        if (!$currentStmt instanceof Stmt || $currentStmt instanceof Use_ || $currentStmt instanceof GroupUse) {
            return [];
        }
        return [new Nop()];
    }
    /**
     * @param Stmt[] $stmts
     * @param Use_[] $newUses
     */
    private function mirrorUseComments(array $stmts, array $newUses, int $indexStmt = 0) : void
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
     * @param array<FullyQualifiedObjectType|AliasedObjectType> $mainTypes
     * @param array<FullyQualifiedObjectType|AliasedObjectType> $typesToRemove
     * @return array<FullyQualifiedObjectType|AliasedObjectType>
     */
    private function diffFullyQualifiedObjectTypes(array $mainTypes, array $typesToRemove) : array
    {
        foreach ($mainTypes as $key => $mainType) {
            foreach ($typesToRemove as $typeToRemove) {
                if ($mainType->equals($typeToRemove)) {
                    unset($mainTypes[$key]);
                }
            }
        }
        return \array_values($mainTypes);
    }
    /**
     * @param array<AliasedObjectType|FullyQualifiedObjectType> $useImportTypes
     * @param array<FullyQualifiedObjectType|AliasedObjectType> $constantUseImportTypes
     * @param array<FullyQualifiedObjectType|AliasedObjectType> $functionUseImportTypes
     * @return Use_[]
     */
    private function createUses(array $useImportTypes, array $constantUseImportTypes, array $functionUseImportTypes, ?string $namespaceName) : array
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
                // already imported in previous cycle
                $newUses[] = $importType->getUseNode($type);
            }
        }
        return $newUses;
    }
    private function getNamespaceName(Namespace_ $namespace) : ?string
    {
        if (!$namespace->name instanceof Name) {
            return null;
        }
        return $namespace->name->toString();
    }
    private function isCurrentNamespace(string $namespaceName, ObjectType $objectType) : bool
    {
        $afterCurrentNamespace = Strings::after($objectType->getClassName(), $namespaceName . '\\');
        if ($afterCurrentNamespace === null) {
            return \false;
        }
        return \strpos($afterCurrentNamespace, '\\') === \false;
    }
}
