<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Node;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\Naming\Naming\AliasNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\FileNode;
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
    private AliasNameResolver $aliasNameResolver;
    public function __construct(ClassNameImportSkipper $classNameImportSkipper, FullyQualifiedNodeMapper $fullyQualifiedNodeMapper, AliasNameResolver $aliasNameResolver)
    {
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->fullyQualifiedNodeMapper = $fullyQualifiedNodeMapper;
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
        // the same end is already imported → skip
        if ($this->classNameImportSkipper->shouldSkipNameForFullyQualifiedObjectType($file, $fullyQualified, $fullyQualifiedObjectType)) {
            return null;
        }
        $fileNode = $file->getFileNode();
        if (!$fileNode instanceof FileNode) {
            return null;
        }
        $pendingImports = $fileNode->getPendingImports();
        if ($pendingImports->isShortImported($fullyQualifiedObjectType)) {
            if ($pendingImports->isImportShortable($fullyQualifiedObjectType)) {
                return $fullyQualifiedObjectType->getShortNameNode();
            }
            return null;
        }
        $this->addUseImport($fileNode, $fullyQualified, $fullyQualifiedObjectType);
        $name = $fullyQualifiedObjectType->getShortNameNode();
        $oldTokens = $file->getOldTokens();
        $startTokenPos = $fullyQualified->getStartTokenPos();
        if (!isset($oldTokens[$startTokenPos])) {
            return $name;
        }
        $tokenShortName = $oldTokens[$startTokenPos];
        if (strncmp($tokenShortName->text, '\\', strlen('\\')) === 0) {
            return $name;
        }
        if (strpos($tokenShortName->text, '\\') !== \false) {
            return $name;
        }
        if ($name->toString() !== $tokenShortName->text) {
            return $name;
        }
        return null;
    }
    private function addUseImport(FileNode $fileNode, FullyQualified $fullyQualified, FullyQualifiedObjectType $fullyQualifiedObjectType): void
    {
        if ($fileNode->hasImport($fullyQualifiedObjectType)) {
            return;
        }
        $pendingImports = $fileNode->getPendingImports();
        if ($fullyQualified->getAttribute(AttributeKey::IS_FUNCCALL_NAME) === \true) {
            $pendingImports->addFunctionUseImport($fullyQualifiedObjectType);
        } elseif ($fullyQualified->getAttribute(AttributeKey::IS_CONSTFETCH_NAME) === \true) {
            $pendingImports->addConstantUseImport($fullyQualifiedObjectType);
        } else {
            $pendingImports->addUseImport($fullyQualifiedObjectType);
        }
    }
}
