<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use RectorPrefix202610\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use PHPStan\Analyser\Scope;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\ValueObject\Application\File;
final class ClassNameImportSkipper
{
    /**
     * @readonly
     */
    private \Rector\CodingStyle\ClassNameImport\ClassNameImportContext $classNameImportContext;
    /**
     * @readonly
     */
    private \Rector\CodingStyle\ClassNameImport\NamespaceBeforeClassNameResolver $namespaceBeforeClassNameResolver;
    /**
     * @readonly
     */
    private UseImportsResolver $useImportsResolver;
    /**
     * @var string[]
     */
    private const RESERVED_CLASS_NAMES = ['bool', 'false', 'float', 'int', 'null', 'parent', 'self', 'static', 'string', 'true', 'void', 'never', 'iterable', 'object', 'mixed', 'array', 'callable'];
    public function __construct(\Rector\CodingStyle\ClassNameImport\ClassNameImportContext $classNameImportContext, \Rector\CodingStyle\ClassNameImport\NamespaceBeforeClassNameResolver $namespaceBeforeClassNameResolver, UseImportsResolver $useImportsResolver)
    {
        $this->classNameImportContext = $classNameImportContext;
        $this->namespaceBeforeClassNameResolver = $namespaceBeforeClassNameResolver;
        $this->useImportsResolver = $useImportsResolver;
    }
    public function shouldSkipNameForFullyQualifiedObjectType(File $file, Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        // cheap, node-local checks first so the file-global lookups are skipped when possible
        return $this->isReservedClassName($fullyQualifiedObjectType) || $this->isNonImportableShortClass($fullyQualifiedObjectType) || $this->shouldStayFullyQualifiedByOriginalName($node) || $this->isConflictingUseImport($file, $fullyQualifiedObjectType) || $this->isConflictingAlias($file, $fullyQualifiedObjectType) || $this->isConflictingClassLikeName($file, $node, $fullyQualifiedObjectType) || $this->isConflictingShortNameUsage($file, $fullyQualifiedObjectType);
    }
    /**
     * @param array<Use_|GroupUse> $uses
     */
    public function shouldSkipName(FullyQualified $fullyQualified, array $uses): bool
    {
        if (substr_count($fullyQualified->toCodeString(), '\\') === 1) {
            return $this->isFunctionOrConstantImport($fullyQualified);
        }
        $stringName = $fullyQualified->toString();
        $lastUseName = $fullyQualified->getLast();
        $nameLastName = strtolower($lastUseName);
        foreach ($uses as $use) {
            $prefix = $this->useImportsResolver->resolvePrefix($use);
            $useName = $prefix . $stringName;
            foreach ($use->uses as $useUse) {
                $useUseLastName = strtolower($useUse->name->getLast());
                if ($useUseLastName !== $nameLastName) {
                    continue;
                }
                if ($this->isConflictedShortNameInUse($useUse, $useName, $lastUseName, $stringName)) {
                    return \true;
                }
                return $prefix . $useUse->name->toString() !== $stringName;
            }
        }
        return \false;
    }
    private function isFunctionOrConstantImport(FullyQualified $fullyQualified): bool
    {
        if ($fullyQualified->getAttribute(AttributeKey::IS_CONSTFETCH_NAME) === \true) {
            return \true;
        }
        return $fullyQualified->getAttribute(AttributeKey::IS_FUNCCALL_NAME) === \true;
    }
    private function isConflictedShortNameInUse(UseItem $useItem, string $useName, string $lastUseName, string $stringName): bool
    {
        if (!$useItem->alias instanceof Identifier && $useName !== $stringName && $lastUseName === $stringName) {
            return \true;
        }
        return $useItem->alias instanceof Identifier && $useItem->alias->toString() === $stringName;
    }
    private function isReservedClassName(FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        return in_array($fullyQualifiedObjectType->getShortNameLowered(), self::RESERVED_CLASS_NAMES, \true);
    }
    /**
     * Short/global class (no namespace) is only importable when explicitly enabled
     */
    private function isNonImportableShortClass(FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $className = ltrim($fullyQualifiedObjectType->getClassName(), '\\');
        if (substr_count($className, '\\') === 0) {
            return !SimpleParameterProvider::provideBoolParameter(Option::IMPORT_SHORT_CLASSES);
        }
        return \false;
    }
    private function shouldStayFullyQualifiedByOriginalName(Node $node): bool
    {
        if (!$node instanceof FullyQualified) {
            return \false;
        }
        if (substr_count($node->toCodeString(), '\\') === 1) {
            return \false;
        }
        // verify long name, as short name verify may conflict
        // see test PR: https://github.com/rectorphp/rector-src/pull/6208
        // ref https://3v4l.org/21H5j vs https://3v4l.org/GIHSB
        $originalName = $node->getAttribute(AttributeKey::ORIGINAL_NAME);
        return $originalName instanceof Name && $originalName->getLast() === $originalName->toString();
    }
    /**
     * Prevents importing App\Some\Product when a different Product is already imported
     */
    private function isConflictingUseImport(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        foreach ($this->classNameImportContext->resolveUsedImportTypes($file) as $useImportType) {
            if (!$useImportType->equals($fullyQualifiedObjectType) && $useImportType->areShortNamesEqual($fullyQualifiedObjectType)) {
                return \true;
            }
            if ($useImportType->equals($fullyQualifiedObjectType)) {
                return \false;
            }
        }
        return \false;
    }
    /**
     * Prevents importing App\SomeClass when there is already "use App\Something as SomeClass"
     */
    private function isConflictingAlias(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $longNameLowered = strtolower($fullyQualifiedObjectType->getClassName());
        $shortNameLowered = $fullyQualifiedObjectType->getShortNameLowered();
        foreach ($this->classNameImportContext->resolveAliasedUses($file) as $aliasedUse) {
            $aliasedUseLowered = strtolower($aliasedUse);
            // its aliased, we cannot just rename it
            if (substr_compare($aliasedUseLowered, '\\' . $shortNameLowered, -strlen('\\' . $shortNameLowered)) === 0) {
                return \true;
            }
            if ($aliasedUseLowered === $shortNameLowered && $longNameLowered === $shortNameLowered) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Prevents importing App\SomeClass when a class-like "SomeClass" is declared in the file
     */
    private function isConflictingClassLikeName(File $file, Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $classLikeNames = $this->classNameImportContext->resolveShortClassLikeNames($file);
        if ($classLikeNames === []) {
            return \false;
        }
        /**
         * Note: Don't use ScopeFetcher::fetch() on Name instance,
         * Scope can be null on Name
         * This is part of ScopeAnalyzer::NON_REFRESHABLE_NODES
         * @see https://github.com/rectorphp/rector-src/blob/9929af7c0179929b4fde6915cb7a06c3141dde6c/src/NodeAnalyzer/ScopeAnalyzer.php#L17
         */
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        $namespace = $scope instanceof Scope ? $scope->getNamespace() : null;
        $namespace = strtolower((string) $namespace);
        $shortNameLowered = $fullyQualifiedObjectType->getShortNameLowered();
        $subClassName = $this->namespaceBeforeClassNameResolver->resolve($fullyQualifiedObjectType);
        $fullyQualifiedObjectTypeNamespace = strtolower($subClassName);
        foreach ($classLikeNames as $classLikeName) {
            if (strtolower($classLikeName) !== $shortNameLowered) {
                continue;
            }
            if ($namespace === '' || $namespace !== $fullyQualifiedObjectTypeNamespace) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Prevents importing App\SomeClass when the short name is already used by a different FQN,
     * e.g. "new X" or "X::static()"
     */
    private function isConflictingShortNameUsage(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $shortNamesToFullyQualifiedNames = $this->classNameImportContext->resolveShortNamesToFullyQualifiedNames($file);
        $fullyQualifiedObjectTypeShortName = $fullyQualifiedObjectType->getShortName();
        $className = $fullyQualifiedObjectType->getClassName();
        foreach ($shortNamesToFullyQualifiedNames as $shortName => $fullyQualifiedName) {
            if ($fullyQualifiedObjectTypeShortName !== $shortName) {
                $shortName = $this->cleanShortName($shortName);
            }
            if ($fullyQualifiedObjectTypeShortName !== $shortName) {
                continue;
            }
            $fullyQualifiedName = ltrim($fullyQualifiedName, '\\');
            return $className !== $fullyQualifiedName;
        }
        return \false;
    }
    private function cleanShortName(string $shortName): string
    {
        return strncmp($shortName, '\\', strlen('\\')) === 0 ? ltrim((string) Strings::after($shortName, '\\', -1)) : $shortName;
    }
}
