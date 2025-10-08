<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use Rector\CodingStyle\ClassNameImport\NamespaceBeforeClassNameResolver;
use Rector\CodingStyle\ClassNameImport\ShortNameResolver;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\ValueObject\Application\File;
/**
 * Prevents adding:
 *
 * use App\SomeClass;
 *
 * If there is already:
 *
 * class SomeClass {}
 */
final class ClassLikeNameClassNameImportSkipVoter implements ClassNameImportSkipVoterInterface
{
    /**
     * @readonly
     */
    private ShortNameResolver $shortNameResolver;
    /**
     * @readonly
     */
    private NamespaceBeforeClassNameResolver $namespaceBeforeClassNameResolver;
    public function __construct(ShortNameResolver $shortNameResolver, NamespaceBeforeClassNameResolver $namespaceBeforeClassNameResolver)
    {
        $this->shortNameResolver = $shortNameResolver;
        $this->namespaceBeforeClassNameResolver = $namespaceBeforeClassNameResolver;
    }
    public function shouldSkip(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node): bool
    {
        $classLikeNames = $this->shortNameResolver->resolveShortClassLikeNames($file);
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
            if ($namespace === '') {
                return \true;
            }
            if ($namespace !== $fullyQualifiedObjectTypeNamespace) {
                return \true;
            }
        }
        return \false;
    }
}
