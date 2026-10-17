<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use Rector\PhpParser\Node\FileNode;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\ValueObject\Application\File;
/**
 * Per-file import facts shared across skip voters. Aliased uses and used import types are
 * file-global, so they are resolved once per file stmts version and reused for every imported name.
 */
final class ClassNameImportContext
{
    /**
     * @readonly
     */
    private \Rector\CodingStyle\ClassNameImport\AliasUsesResolver $aliasUsesResolver;
    /**
     * @readonly
     */
    private \Rector\CodingStyle\ClassNameImport\ShortNameResolver $shortNameResolver;
    private ?File $cachedFile = null;
    private int $cachedVersion = -1;
    /**
     * @var string[]
     */
    private array $aliasedUses = [];
    /**
     * @var array<AliasedObjectType|FullyQualifiedObjectType>
     */
    private array $usedImportTypes = [];
    public function __construct(\Rector\CodingStyle\ClassNameImport\AliasUsesResolver $aliasUsesResolver, \Rector\CodingStyle\ClassNameImport\ShortNameResolver $shortNameResolver)
    {
        $this->aliasUsesResolver = $aliasUsesResolver;
        $this->shortNameResolver = $shortNameResolver;
    }
    /**
     * Short names actually used in the file (class-likes, names, docblock types) mapped to their FQN
     *
     * @return array<string, string>
     */
    public function resolveShortNamesToFullyQualifiedNames(File $file): array
    {
        return $this->shortNameResolver->resolveFromFile($file);
    }
    /**
     * Short names of class-likes (class, interface, trait) declared in the file
     *
     * @return string[]
     */
    public function resolveShortClassLikeNames(File $file): array
    {
        return $this->shortNameResolver->resolveShortClassLikeNames($file);
    }
    /**
     * @return string[]
     */
    public function resolveAliasedUses(File $file): array
    {
        $this->ensureResolved($file);
        return $this->aliasedUses;
    }
    /**
     * @return array<AliasedObjectType|FullyQualifiedObjectType>
     */
    public function resolveUsedImportTypes(File $file): array
    {
        $this->ensureResolved($file);
        return $this->usedImportTypes;
    }
    private function ensureResolved(File $file): void
    {
        $newStmtsVersion = $file->getNewStmtsVersion();
        if ($file === $this->cachedFile && $newStmtsVersion === $this->cachedVersion) {
            return;
        }
        $this->cachedFile = $file;
        $this->cachedVersion = $newStmtsVersion;
        $this->aliasedUses = $this->resolveAliasedUsesFromFile($file);
        $this->usedImportTypes = $this->resolveUsedImportTypesFromFile($file);
    }
    /**
     * @return string[]
     */
    private function resolveAliasedUsesFromFile(File $file): array
    {
        /** @var Namespace_[]|FileNode[] $namespaces */
        $namespaces = array_filter($file->getNewStmts(), static fn(Stmt $stmt): bool => $stmt instanceof Namespace_ || $stmt instanceof FileNode);
        if (count($namespaces) !== 1) {
            return [];
        }
        $namespace = current($namespaces);
        return $this->aliasUsesResolver->resolveFromStmts($namespace->stmts);
    }
    /**
     * @return array<AliasedObjectType|FullyQualifiedObjectType>
     */
    private function resolveUsedImportTypesFromFile(File $file): array
    {
        $fileNode = $file->getFileNode();
        if (!$fileNode instanceof FileNode) {
            return [];
        }
        return $fileNode->resolveUsedImportTypes();
    }
}
